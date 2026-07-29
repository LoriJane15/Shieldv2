<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserLogoService
{
    public function store(UploadedFile $logo): string
    {
        return $logo->store('logos', 'public');
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'logos/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
