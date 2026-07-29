<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AgencyLogoService
{
    public function store(UploadedFile $logo): string
    {
        return $logo->store('agency-logos', 'public');
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'agency-logos/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
