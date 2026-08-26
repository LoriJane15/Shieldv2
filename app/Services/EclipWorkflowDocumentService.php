<?php

namespace App\Services;

use App\Models\EclipWorkflowActivity;
use App\Models\EclipWorkflowDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EclipWorkflowDocumentService
{
    public function store(EclipWorkflowActivity $activity, string $documentType, UploadedFile $file, ?string $remarks, User $actor, ?string $ipAddress): EclipWorkflowDocument
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $path = $file->storeAs("eclip/{$activity->eclip_case_id}/workflow/{$activity->id}", Str::uuid().'.'.$extension, 'local');
        throw_if($path === false, \RuntimeException::class, 'The workflow evidence could not be stored.');

        try {
            return DB::transaction(function () use ($activity, $documentType, $file, $remarks, $actor, $ipAddress, $path) {
                $locked = EclipWorkflowActivity::query()->lockForUpdate()->findOrFail($activity->id);
                $version = ((int) $locked->documents()->where('document_type', $documentType)->max('version_number')) + 1;
                $originalName = $this->safeOriginalName($file);
                $document = $locked->documents()->create([
                    'uploaded_by' => $actor->id,
                    'document_type' => $documentType,
                    'version_number' => $version,
                    'storage_path' => $path,
                    'original_name' => $originalName,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'remarks' => $remarks,
                ]);

                $locked->histories()->create([
                    ...$this->actorContext($actor),
                    'event' => 'document_uploaded',
                    'from_status' => $locked->status,
                    'to_status' => $locked->status,
                    'remarks' => $remarks,
                    'document_id' => $document->id,
                    'data' => [
                        'document_type' => $documentType,
                        'version_number' => $version,
                        'original_name' => $originalName,
                        'sha256' => $document->sha256,
                    ],
                    'ip_address' => $ipAddress,
                ]);

                return $document;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function actorContext(?User $actor): array
    {
        if (! $actor) {
            return ['user_id' => null, 'actor_role' => 'system', 'actor_office' => 'SHIELD 2.0'];
        }

        $actor->loadMissing(['municipality', 'govAgency']);

        return [
            'user_id' => $actor->id,
            'actor_role' => $actor->role,
            'actor_office' => $actor->govAgency?->name
                ?? $actor->municipality?->name
                ?? config("shield.roles.{$actor->role}.label", str($actor->role)->replace('_', ' ')->title()->toString()),
        ];
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        return preg_replace('/[^\pL\pN._ -]/u', '_', $name) ?: 'workflow-evidence.'.strtolower($file->extension() ?: 'bin');
    }
}
