<?php

namespace App\Services;

use App\Models\EclipCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipFundProofStorageService
{
    public function store(UploadedFile $file, EclipCase $case): string
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $path = $file->storeAs("eclip/{$case->id}/funding", Str::uuid().'.'.$extension, 'local');
        throw_if($path === false, \RuntimeException::class, 'The funding proof could not be stored.');

        return $path;
    }

    public function download(string $path, string $name): StreamedResponse
    {
        abort_unless(str_starts_with($path, 'eclip/') && ! str_contains($path, '..') && Storage::disk('local')->exists($path), 404);

        $mimeType = Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);

        return Storage::disk('local')->response($path, basename($name), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'eclip/') && ! str_contains($path, '..')) {
            Storage::disk('local')->delete($path);
        }
    }
}
