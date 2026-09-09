<?php

namespace App\Http\Controllers\Japic;

use App\Http\Controllers\Controller;
use App\Models\JapicCertificationProcessing;
use App\Services\JapicCertificationDocumentService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CertificationDocumentController extends Controller
{
    public function preview(JapicCertificationProcessing $japicCertificationProcessing, JapicCertificationDocumentService $documents): Response
    {
        Gate::authorize('previewDraft', $japicCertificationProcessing);

        return $this->render($documents->data($japicCertificationProcessing), false);
    }

    public function print(JapicCertificationProcessing $japicCertificationProcessing, JapicCertificationDocumentService $documents): Response
    {
        Gate::authorize('printDraft', $japicCertificationProcessing);

        return $this->render($documents->data($japicCertificationProcessing), true);
    }

    private function render(array $data, bool $printMode): Response
    {
        return response()->view('japic.certifications.document', [...$data, 'printMode' => $printMode])->withHeaders([
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0', 'Pragma' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
