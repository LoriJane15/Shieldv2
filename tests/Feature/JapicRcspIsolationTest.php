<?php

namespace Tests\Feature;

use App\Models\JapicCertificationDocumentVersion;
use App\Models\JapicCertificationProcessing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JapicRcspIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_japic_schema_models_and_services_have_no_rcsp_dependency(): void
    {
        foreach (['japic_certification_processings', 'japic_certification_drafts',
            'japic_certification_draft_histories', 'japic_certification_histories',
            'japic_certification_document_versions'] as $table) {
            foreach (DB::select("PRAGMA foreign_key_list('{$table}')") as $foreignKey) {
                $this->assertFalse(str_starts_with($foreignKey->table, 'rcsp_'));
            }
        }
        $this->assertFalse(method_exists(new JapicCertificationProcessing, 'rcsp'));
        $this->assertFalse(method_exists(new JapicCertificationDocumentVersion, 'rcsp'));

        foreach (['JapicCertificationIntakeService.php', 'JapicCertificationCancellationCoordinator.php'] as $file) {
            $this->assertStringNotContainsString('Rcsp', file_get_contents(app_path('Services/'.$file)));
            $this->assertStringNotContainsString('rcsp_', strtolower(file_get_contents(app_path('Services/'.$file))));
        }
    }
}
