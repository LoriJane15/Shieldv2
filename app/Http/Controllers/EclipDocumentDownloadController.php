<?php

namespace App\Http\Controllers;

use App\Models\EclipDocumentVersion;
use App\Services\DocumentAccessLogger;
use App\Services\EclipDocumentStorageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipDocumentDownloadController extends Controller
{
    public function __invoke(Request $request, EclipDocumentVersion $version, EclipDocumentStorageService $storage, DocumentAccessLogger $accessLog): StreamedResponse
    {
        $version->loadMissing('document.eclipCase');
        $this->authorize('downloadDocument', $version->document->eclipCase);

        $response = $storage->preview($version->storage_path, $version->original_name, $version->mime_type);
        $accessLog->record($request->user(), $version->document, 'download', $version->id, $request->ip(), $request->userAgent());

        return $response;
    }
}
