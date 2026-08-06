<?php

namespace App\Services;

use App\Models\EclipBasicService;
use App\Models\EclipBasicServiceDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipBasicServiceDocumentService
{
    public function store(EclipBasicService $service, UploadedFile $file, User $actor, ?string $ipAddress): EclipBasicServiceDocument
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $path = $file->storeAs("private/eclip/basic-services/{$service->id}", Str::uuid().'.'.$extension, 'local');

        try {
            return DB::transaction(function () use ($service, $file, $actor, $ipAddress, $path) {
                $locked = EclipBasicService::query()->lockForUpdate()->findOrFail($service->id);
                $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
                $originalName = preg_replace('/[^\pL\pN._ -]/u', '_', $originalName) ?: 'supporting-document';
                $originalName = mb_substr($originalName, 0, 240);
                $document = $locked->documents()->create([
                    'version_number' => ((int) $locked->documents()->max('version_number')) + 1,
                    'storage_path' => $path,
                    'original_name' => $originalName,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $actor->id,
                ]);
                $locked->histories()->create(['user_id' => $actor->id, 'action' => 'document_uploaded', 'new_values' => ['document_id' => $document->id, 'version_number' => $document->version_number], 'ip_address' => $ipAddress]);

                return $document;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function download(EclipBasicServiceDocument $document): StreamedResponse
    {
        $managedPrefix = "private/eclip/basic-services/{$document->basic_service_id}/";
        abort_unless(str_starts_with($document->storage_path, $managedPrefix) && ! str_contains($document->storage_path, '..') && Storage::disk('local')->exists($document->storage_path), 404);
        abort_unless(in_array($document->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);

        return Storage::disk('local')->response($document->storage_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
