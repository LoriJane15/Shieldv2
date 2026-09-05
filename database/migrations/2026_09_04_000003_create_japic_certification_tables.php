<?php

use App\Enums\JapicCertificationDocumentSource;
use App\Enums\JapicCertificationHistoryEvent;
use App\Enums\JapicCertificationStatus;
use App\Enums\JapicCertificationTriggerSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('japic_certification_processings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ib39_surfaced_former_rebel_id')
                ->unique()
                ->constrained('ib39_surfaced_former_rebels')
                ->restrictOnDelete();
            $table->foreignId('triggering_cdr_document_version_id')
                ->constrained('ib39_cdr_document_versions')
                ->restrictOnDelete();
            $table->enum('status', array_column(JapicCertificationStatus::cases(), 'value'))
                ->default(JapicCertificationStatus::Pending->value)
                ->index();
            $table->date('received_on')->index();
            $table->date('due_on')->index();
            $table->enum('trigger_source', array_column(JapicCertificationTriggerSource::cases(), 'value'))->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->text('control_number')->nullable();
            $table->char('control_number_hash', 64)->nullable()->unique();
            $table->text('delay_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_on'], 'japic_certification_status_due_index');
        });

        Schema::create('japic_certification_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('japic_certification_processing_id')
                ->unique()
                ->constrained('japic_certification_processings')
                ->restrictOnDelete();
            $table->text('content')->nullable();
            $table->unsignedInteger('schema_version')->default(1);
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('fr_photo_version_id')->nullable()->constrained('ib39_cdr_photo_versions')->restrictOnDelete();
            $table->foreignId('last_saved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('japic_certification_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('japic_certification_processing_id')
                ->constrained('japic_certification_processings')
                ->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->enum('source', array_column(JapicCertificationDocumentSource::cases(), 'value'))->index();
            $table->foreignId('replaces_version_id')
                ->nullable()
                ->constrained('japic_certification_document_versions')
                ->restrictOnDelete();
            $table->text('replacement_reason')->nullable();
            $table->string('storage_path', 500);
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->boolean('confirmed_correct_fr');
            $table->boolean('confirmed_complete');
            $table->boolean('confirmed_signatures_present');
            $table->boolean('confirmed_final_copy');
            $table->timestamp('confirmed_at');
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->unique(
                ['japic_certification_processing_id', 'version_number'],
                'japic_certification_processing_version_unique'
            );
        });

        Schema::create('japic_certification_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('japic_certification_processing_id')
                ->constrained('japic_certification_processings')
                ->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('from_status', array_column(JapicCertificationStatus::cases(), 'value'))->nullable();
            $table->enum('to_status', array_column(JapicCertificationStatus::cases(), 'value'));
            $table->enum('event', array_column(JapicCertificationHistoryEvent::cases(), 'value'));
            $table->text('remarks')->nullable();
            $table->text('delay_reason')->nullable();
            $table->foreignId('japic_certification_document_version_id')
                ->nullable()
                ->constrained('japic_certification_document_versions')
                ->restrictOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(
                ['japic_certification_processing_id', 'created_at'],
                'japic_certification_history_processing_created_index'
            );
        });

        Schema::table('japic_certification_processings', function (Blueprint $table) {
            $table->foreignId('current_final_version_id')
                ->nullable()
                ->constrained('japic_certification_document_versions')
                ->restrictOnDelete();
        });

        $this->createRelationshipGuards();
    }

    private function createRelationshipGuards(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        foreach (['INSERT' => 'insert', 'UPDATE OF ib39_surfaced_former_rebel_id, triggering_cdr_document_version_id, received_on, due_on' => 'update'] as $operation => $suffix) {
            DB::unprepared("CREATE TRIGGER japic_processing_eligibility_{$suffix}
                BEFORE {$operation} ON japic_certification_processings
                WHEN DATE(NEW.due_on) != DATE(NEW.received_on, '+14 days')
                    OR EXISTS (
                        SELECT 1 FROM ib39_fr_cancellations cancellation
                        WHERE cancellation.ib39_surfaced_former_rebel_id = NEW.ib39_surfaced_former_rebel_id
                    )
                    OR NOT EXISTS (
                        SELECT 1
                        FROM ib39_cdr_document_versions version
                        JOIN ib39_cdr_processings cdr ON cdr.id = version.cdr_processing_id
                        WHERE version.id = NEW.triggering_cdr_document_version_id
                            AND cdr.ib39_surfaced_former_rebel_id = NEW.ib39_surfaced_former_rebel_id
                            AND cdr.status = 'Completed'
                            AND cdr.current_final_version_id = version.id
                    )
                BEGIN
                    SELECT RAISE(ABORT, 'Invalid JAPIC certification processing eligibility');
                END");
        }

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER japic_current_final_version_guard
            BEFORE UPDATE OF current_final_version_id ON japic_certification_processings
            WHEN NEW.current_final_version_id IS NOT NULL AND NOT EXISTS (
                SELECT 1 FROM japic_certification_document_versions version
                WHERE version.id = NEW.current_final_version_id
                    AND version.japic_certification_processing_id = NEW.id
            )
            BEGIN
                SELECT RAISE(ABORT, 'JAPIC current final version belongs to another processing record');
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER japic_replacement_version_guard
            BEFORE INSERT ON japic_certification_document_versions
            WHEN NEW.replaces_version_id IS NOT NULL AND (
                TRIM(COALESCE(NEW.replacement_reason, '')) = ''
                OR NOT EXISTS (
                    SELECT 1 FROM japic_certification_document_versions replaced
                    WHERE replaced.id = NEW.replaces_version_id
                        AND replaced.japic_certification_processing_id = NEW.japic_certification_processing_id
                )
            )
            BEGIN
                SELECT RAISE(ABORT, 'Invalid JAPIC certification document replacement');
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER japic_history_document_version_guard
            BEFORE INSERT ON japic_certification_histories
            WHEN NEW.japic_certification_document_version_id IS NOT NULL AND NOT EXISTS (
                SELECT 1 FROM japic_certification_document_versions version
                WHERE version.id = NEW.japic_certification_document_version_id
                    AND version.japic_certification_processing_id = NEW.japic_certification_processing_id
            )
            BEGIN
                SELECT RAISE(ABORT, 'JAPIC history document version belongs to another processing record');
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER japic_draft_photo_version_insert_guard
            BEFORE INSERT ON japic_certification_drafts
            WHEN NEW.fr_photo_version_id IS NOT NULL AND NOT EXISTS (
                SELECT 1
                FROM japic_certification_processings japic
                JOIN ib39_cdr_processings cdr
                    ON cdr.ib39_surfaced_former_rebel_id = japic.ib39_surfaced_former_rebel_id
                JOIN ib39_cdr_photos photo
                    ON photo.cdr_processing_id = cdr.id AND photo.photo_type = 'fr_photo'
                JOIN ib39_cdr_photo_versions version
                    ON version.cdr_photo_id = photo.id
                WHERE japic.id = NEW.japic_certification_processing_id
                    AND version.id = NEW.fr_photo_version_id
            )
            BEGIN
                SELECT RAISE(ABORT, 'JAPIC draft photo belongs to another surfaced FR');
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER japic_draft_photo_version_guard
            BEFORE UPDATE OF fr_photo_version_id ON japic_certification_drafts
            WHEN NEW.fr_photo_version_id IS NOT NULL AND NOT EXISTS (
                SELECT 1
                FROM japic_certification_processings japic
                JOIN ib39_cdr_processings cdr
                    ON cdr.ib39_surfaced_former_rebel_id = japic.ib39_surfaced_former_rebel_id
                JOIN ib39_cdr_photos photo
                    ON photo.cdr_processing_id = cdr.id AND photo.photo_type = 'fr_photo'
                JOIN ib39_cdr_photo_versions version
                    ON version.cdr_photo_id = photo.id
                WHERE japic.id = NEW.japic_certification_processing_id
                    AND version.id = NEW.fr_photo_version_id
            )
            BEGIN
                SELECT RAISE(ABORT, 'JAPIC draft photo belongs to another surfaced FR');
            END
        SQL);
    }

    public function down(): void
    {
        $this->dropRelationshipGuards();

        Schema::table('japic_certification_processings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_final_version_id');
        });

        Schema::dropIfExists('japic_certification_histories');
        Schema::dropIfExists('japic_certification_document_versions');
        Schema::dropIfExists('japic_certification_drafts');
        Schema::dropIfExists('japic_certification_processings');
    }

    private function dropRelationshipGuards(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        foreach ([
            'japic_processing_eligibility_insert',
            'japic_processing_eligibility_update',
            'japic_current_final_version_guard',
            'japic_replacement_version_guard',
            'japic_history_document_version_guard',
            'japic_draft_photo_version_insert_guard',
            'japic_draft_photo_version_guard',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
