<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AgencyLogoService
{
    public function store(UploadedFile $logo): array
    {
        $path = $logo->store('agency-logos', 'public');
        if (! is_string($path) || ! $this->isManagedPath($path)) {
            throw new RuntimeException('The agency logo could not be stored safely.');
        }

        return [
            'profile' => $path,
            'logo_original_name' => Str::limit(basename(str_replace('\\', '/', $logo->getClientOriginalName())), 255, ''),
            'logo_mime_type' => $logo->getMimeType(),
            'logo_size_bytes' => $logo->getSize(),
        ];
    }

    public function delete(?string $path): void
    {
        if (! $this->isManagedPath($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function isManagedPath(?string $path): bool
    {
        if (! is_string($path) || ! Str::startsWith($path, 'agency-logos/')) {
            return false;
        }

        return ! str_contains($path, '..')
            && ! str_contains($path, '\\')
            && preg_match('#^agency-logos/[A-Za-z0-9._-]+$#', $path) === 1;
    }

    public function isSafeLegacyPath(?string $path): bool
    {
        return is_string($path)
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9 _().-]*$/', $path) === 1;
    }
}
