<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\GovAgency;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AgencyManagementService
{
    private const AUDITED_FIELDS = [
        'name', 'acronym', 'profile', 'logo_original_name', 'logo_mime_type', 'logo_size_bytes',
    ];

    public function __construct(private readonly AgencyLogoService $logos) {}

    public function create(array $data, ?UploadedFile $logo, User $actor, ?string $ipAddress, ?string $userAgent): GovAgency
    {
        unset($data['profile']);
        $logoMetadata = $logo ? $this->logos->store($logo) : null;
        if ($logoMetadata) {
            $data = [...$data, ...$logoMetadata];
        }

        try {
            return DB::transaction(function () use ($data, $logoMetadata, $actor, $ipAddress, $userAgent) {
                $agency = GovAgency::query()->create($data);
                $this->audit($agency, $actor, 'agency_created', null, $this->values($agency), $ipAddress, $userAgent);

                if ($logoMetadata) {
                    $this->audit($agency, $actor, 'agency_logo_uploaded', null, $logoMetadata, $ipAddress, $userAgent);
                }

                return $agency;
            });
        } catch (\Throwable $exception) {
            $this->logos->delete($logoMetadata['profile'] ?? null);

            throw $exception;
        }
    }

    public function update(GovAgency $agency, array $data, ?UploadedFile $logo, User $actor, ?string $ipAddress, ?string $userAgent): GovAgency
    {
        unset($data['profile']);
        $oldLogo = $agency->profile;
        $before = $this->values($agency);
        $logoMetadata = $logo ? $this->logos->store($logo) : null;
        if ($logoMetadata) {
            $data = [...$data, ...$logoMetadata];
        }

        try {
            $updated = DB::transaction(function () use ($agency, $data, $logoMetadata, $actor, $before, $ipAddress, $userAgent) {
                $agency->update($data);
                $agency->refresh();
                $this->audit($agency, $actor, $logoMetadata ? 'agency_logo_replaced' : 'agency_updated', $before, $this->values($agency), $ipAddress, $userAgent);

                return $agency;
            });
        } catch (\Throwable $exception) {
            $this->logos->delete($logoMetadata['profile'] ?? null);

            throw $exception;
        }

        if ($logoMetadata) {
            $this->logos->delete($oldLogo);
        }

        return $updated;
    }

    public function delete(GovAgency $agency, User $actor, ?string $ipAddress, ?string $userAgent): void
    {
        $logo = $agency->profile;
        DB::transaction(function () use ($agency, $actor, $ipAddress, $userAgent) {
            $this->audit($agency, $actor, 'agency_deleted', $this->values($agency), null, $ipAddress, $userAgent);
            $agency->delete();
        });
        $this->logos->delete($logo);
    }

    private function values(GovAgency $agency): array
    {
        return Arr::only($agency->attributesToArray(), self::AUDITED_FIELDS);
    }

    private function audit(GovAgency $agency, User $actor, string $action, ?array $before, ?array $after, ?string $ipAddress, ?string $userAgent): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'entity_type' => GovAgency::class,
            'entity_id' => $agency->id,
            'previous_values' => $before,
            'new_values' => $after,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
