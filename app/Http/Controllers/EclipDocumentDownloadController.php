<?php

namespace App\Http\Controllers;

use App\Models\EclipDocumentVersion;
use App\Services\EclipDocumentStorageService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipDocumentDownloadController extends Controller
{
    public function __invoke(EclipDocumentVersion $version, EclipDocumentStorageService $storage): StreamedResponse
    {
        $version->loadMissing('document.eclipCase');
        $this->authorize('downloadDocument', $version->document->eclipCase);

        return $storage->preview($version->storage_path, $version->original_name, $version->mime_type);
    }
}
