<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Models\EclipBasicServiceDocument;
use App\Models\EclipDocumentVersion;
use Illuminate\View\View;

class EclipDocumentPreviewController extends Controller
{
    public function caseDocument(EclipDocumentVersion $version): View
    {
        $version->loadMissing(['document.eclipCase.formerRebel', 'document.requirement', 'uploader']);
        $case = $version->document->eclipCase;
        $this->authorize('downloadDocument', $case);

        return view('lswdo.eclip.document-preview', [
            'title' => $version->document->requirement->name,
            'case' => $case,
            'filename' => $version->original_name,
            'mimeType' => $version->mime_type,
            'sizeBytes' => $version->size_bytes,
            'versionNumber' => $version->version_number,
            'uploadedBy' => $version->uploader?->name,
            'uploadedAt' => $version->created_at,
            'status' => $version->document->status,
            'contentUrl' => route('lswdo.eclip.documents.download', $version),
            'backUrl' => route('lswdo.eclip.show', $case),
        ]);
    }

    public function basicServiceDocument(EclipBasicServiceDocument $document): View
    {
        $document->loadMissing(['service.eclipCase.formerRebel', 'uploader']);
        $this->authorize('downloadDocument', $document->service);
        $case = $document->service->eclipCase;
        $types = config('shield.eclip_basic_service_types', []);

        return view('lswdo.eclip.document-preview', [
            'title' => ($types[$document->service->service_type] ?? str($document->service->service_type)->headline()).' Evidence',
            'case' => $case,
            'filename' => $document->original_name,
            'mimeType' => $document->mime_type,
            'sizeBytes' => $document->size_bytes,
            'versionNumber' => $document->version_number,
            'uploadedBy' => $document->uploader?->name,
            'uploadedAt' => $document->created_at,
            'status' => $document->service->status,
            'contentUrl' => route('lswdo.eclip.basic-services.documents.download', $document),
            'backUrl' => route('lswdo.eclip.basic-services.index', $case),
        ]);
    }
}
