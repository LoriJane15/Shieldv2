<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistanceCertificateService
{
    private const DIRECTORY = 'fr/certificates';

    public function store(UploadedFile $file): string
    {
        return $file->store(self::DIRECTORY, 'local');
    }

    public function download(string $path): StreamedResponse
    {
        if ($this->isManagedPath($path) && Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        // Preserve access to certificates imported before private storage was enabled.
        abort_unless($this->isManagedPath($path) && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }

    public function delete(?string $path): void
    {
        if (! $path || ! $this->isManagedPath($path)) {
            return;
        }

        Storage::disk('local')->delete($path);
        Storage::disk('public')->delete($path);
    }

    private function isManagedPath(string $path): bool
    {
        return str_starts_with($path, self::DIRECTORY.'/')
            && ! str_contains($path, '..');
    }
}
