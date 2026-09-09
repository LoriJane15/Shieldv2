<?php

namespace App\Support;

use App\Models\JapicCertificationProcessing;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class JapicCertificationDraftSchema
{
    public const VERSION = 1;

    public const PURPOSE = 'This certification is being issued to attest her legitimacy as a former rebel to support her application for the Enhanced Comprehensive Local Integration Program(E-CLIP).';

    public const POSITIONS = [
        'provincial_afp' => 'AFP Co-Chairman, JAPIC-Provincial',
        'provincial_pnp' => 'PNP Co-Chairman, JAPIC-Provincial',
        'area_afp' => 'AFP Co-Chairman, JAPIC-Area',
        'area_pnp' => 'PNP Co-Chairman, JAPIC-Area',
    ];

    public function sourceSnapshot(JapicCertificationProcessing $processing): array
    {
        $processing->loadMissing(['surfacedFormerRebel.municipality', 'surfacedFormerRebel.barangay', 'triggeringCdrDocumentVersion.processing']);
        $fr = $processing->surfacedFormerRebel;
        $version = $processing->triggeringCdrDocumentVersion;
        abort_unless($version && $version->processing?->ib39_surfaced_former_rebel_id === $fr->id, 409, 'The authoritative CDR source is unavailable.');
        $snapshot = $version->content_snapshot ?? [];
        $content = $snapshot['content'] ?? [];
        $gender = $this->clean($content['gender'] ?? null);
        $areas = collect($content['posting_areas'] ?? [])->pluck('place')->map(fn ($v) => $this->clean($v))->filter()->unique()->values()->all();
        $location = collect([$fr->specific_location, $fr->barangay?->name, $fr->municipality?->name, $fr->province])->filter()->implode(', ');

        return [
            'fr_id' => $fr->id,
            'fr_reference' => $fr->reference_number,
            'triggering_cdr_document_version_id' => $version->id,
            'subject_photo_version_id' => isset($snapshot['fr_photo_version_id']) ? (int) $snapshot['fr_photo_version_id'] : null,
            'subject_name' => $fr->display_name,
            'alias' => $this->clean($content['alias'] ?? null),
            'gender' => $gender,
            'pronouns' => match (Str::lower((string) $gender)) {
                'female' => ['subject' => 'she', 'object' => 'her', 'possessive' => 'her'],
                'male' => ['subject' => 'he', 'object' => 'him', 'possessive' => 'his'],
                default => ['subject' => 'they', 'object' => 'them', 'possessive' => 'their'],
            },
            'classification' => $this->clean($content['classification'] ?? null),
            'residential_address' => $this->clean($content['present_address'] ?? null) ?: $location,
            'former_position' => $this->clean($content['latest_position'] ?? $content['position_held'] ?? null),
            'former_organization' => $this->clean($content['organization_affiliation'] ?? $content['individual_affiliation'] ?? null),
            'operating_areas' => $areas,
            'affiliation_period' => $this->clean($content['recruitment_date'] ?? $content['duration_with_group'] ?? null),
            'surfacing_date' => $fr->surfaced_at?->format('Y-m-d'),
            'surfacing_location' => $location,
        ];
    }

    public function normalize(array $manual, array $source): array
    {
        $certificate = Arr::only($manual['certificate'] ?? [], [
            'date_issued', 'surrendering_unit', 'surrender_date', 'surrender_location', 'operating_area_supplement',
        ]);
        $signatories = [];
        foreach (array_keys(self::POSITIONS) as $key) {
            $signatories[$key] = Arr::only($manual['signatories'][$key] ?? [], ['rank', 'name', 'suffix']);
        }

        return [
            'schema_version' => self::VERSION,
            'source_snapshot' => $this->normalizeValue($source),
            'certificate' => $this->normalizeValue($certificate),
            'signatories' => $this->normalizeValue($signatories),
        ];
    }

    public function fingerprint(array $payload): string
    {
        return hash_hmac('sha256', json_encode($this->normalizeValue($payload), JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function controlNumberHash(string $controlNumber): string
    {
        $normalized = Str::lower(Str::squish(Str::of($controlNumber)->trim()->toString()));

        return hash_hmac('sha256', 'japic-control-number:'.$normalized, (string) config('app.key'));
    }

    public function missingForSigning(array $payload, ?string $controlNumber): array
    {
        $paths = [
            'control_number' => $controlNumber,
            'certificate.date_issued' => data_get($payload, 'certificate.date_issued'),
            'certificate.surrendering_unit' => data_get($payload, 'certificate.surrendering_unit'),
            'certificate.surrender_date' => data_get($payload, 'certificate.surrender_date'),
            'certificate.surrender_location' => data_get($payload, 'certificate.surrender_location'),
            'source_snapshot.subject_name' => data_get($payload, 'source_snapshot.subject_name'),
            'source_snapshot.alias' => data_get($payload, 'source_snapshot.alias'),
            'source_snapshot.classification' => data_get($payload, 'source_snapshot.classification'),
            'source_snapshot.residential_address' => data_get($payload, 'source_snapshot.residential_address'),
            'source_snapshot.former_position' => data_get($payload, 'source_snapshot.former_position'),
            'source_snapshot.former_organization' => data_get($payload, 'source_snapshot.former_organization'),
            'source_snapshot.affiliation_period' => data_get($payload, 'source_snapshot.affiliation_period'),
        ];
        foreach (array_keys(self::POSITIONS) as $key) {
            $paths["signatories.{$key}.rank"] = data_get($payload, "signatories.{$key}.rank");
            $paths["signatories.{$key}.name"] = data_get($payload, "signatories.{$key}.name");
        }

        return array_keys(array_filter($paths, fn ($value) => blank($value)));
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = preg_replace('/\R/u', "\n", trim($value));

            return $value === '' ? null : (preg_match('/\n/u', $value) ? $value : Str::squish($value));
        }
        if (! is_array($value)) {
            return $value;
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($item) => $this->normalizeValue($item), $value);
    }

    private function clean(mixed $value): ?string
    {
        return is_scalar($value) ? $this->normalizeValue((string) $value) : null;
    }
}
