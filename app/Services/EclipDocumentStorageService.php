<?php

namespace App\Services;

use App\Models\EclipDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipDocumentStorageService
{
    public function store(UploadedFile $file, EclipDocument $document): string
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $directory = "eclip/{$document->eclip_case_id}/{$document->id}";

        $path = $file->storeAs($directory, Str::uuid().'.'.$extension, 'local');

        throw_if($path === false, \RuntimeException::class, 'The E-CLIP document could not be stored.');

        return $path;
    }

    public function storeFea(UploadedFile $file, int $caseId): string
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $path = $file->storeAs("eclip/{$caseId}/fea", Str::uuid().'.'.$extension, 'local');

        throw_if($path === false, \RuntimeException::class, 'The FEA document could not be stored.');

        return $path;
    }

    public function preview(string $path, string $displayName, string $mimeType): StreamedResponse
    {
        abort_unless($this->isManagedPath($path) && Storage::disk('local')->exists($path), 404);

        abort_unless(in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);

        return Storage::disk('local')->response($path, basename($displayName), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function download(string $path, string $displayName, string $mimeType = 'application/octet-stream'): StreamedResponse
    {
        abort_unless($this->isManagedPath($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($displayName), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function delete(?string $path): void
    {
        if ($path && $this->isManagedPath($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    private function isManagedPath(string $path): bool
    {
        return str_starts_with($path, 'eclip/') && ! str_contains($path, '..');
    }
}
