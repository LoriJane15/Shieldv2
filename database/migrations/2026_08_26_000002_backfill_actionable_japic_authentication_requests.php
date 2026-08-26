<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKFILL_REMARKS = 'Compatibility request created from an existing actionable Step 4A JAPIC assignment.';

    public function up(): void
    {
        if (! Schema::hasTable('eclip_authentication_requests')
            || ! Schema::hasTable('eclip_authentication_histories')
            || ! Schema::hasTable('eclip_workflow_activities')
            || ! Schema::hasTable('eclip_case_participants')) {
            return;
        }

        $assignments = DB::table('eclip_case_participants as participants')
            ->join('eclip_workflow_activities as activities', function ($join) {
                $join->on('activities.eclip_case_id', '=', 'participants.eclip_case_id')
                    ->where('activities.step_code', '4A')
                    ->whereIn('activities.status', ['pending', 'ongoing', 'late', 'returned_for_correction']);
            })
            ->join('eclip_cases as cases', 'cases.id', '=', 'participants.eclip_case_id')
            ->leftJoin('eclip_authentication_requests as requests', 'requests.eclip_case_id', '=', 'participants.eclip_case_id')
            ->where('participants.participant_role', 'authentication_reviewer')
            ->where('participants.is_active', true)
            ->whereNull('requests.id')
            ->orderBy('participants.id')
            ->get([
                'participants.eclip_case_id',
                'participants.user_id as assigned_to',
                'cases.assigned_to as case_processor_id',
                'cases.created_by',
                'activities.status as activity_status',
                'activities.available_at',
                'activities.started_at',
                'activities.due_at',
            ])
            ->unique('eclip_case_id');

        foreach ($assignments as $assignment) {
            $requestedBy = $assignment->case_processor_id ?: $assignment->created_by;
            $timestamp = now();
            $requestId = DB::table('eclip_authentication_requests')->insertGetId([
                'eclip_case_id' => $assignment->eclip_case_id,
                'requested_by' => $requestedBy,
                'assigned_to' => $assignment->assigned_to,
                'status' => $assignment->activity_status === 'ongoing' ? 'under_review' : 'pending',
                'requested_at' => $assignment->available_at ?: $timestamp,
                'started_at' => $assignment->activity_status === 'ongoing' ? ($assignment->started_at ?: $timestamp) : null,
                'due_at' => $assignment->due_at,
                'remarks' => self::BACKFILL_REMARKS,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::table('eclip_authentication_histories')->insert([
                'authentication_request_id' => $requestId,
                'user_id' => $requestedBy,
                'to_status' => $assignment->activity_status === 'ongoing' ? 'under_review' : 'pending',
                'remarks' => self::BACKFILL_REMARKS,
                'data' => json_encode(['source' => 'official_step_4a_assignment'], JSON_THROW_ON_ERROR),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('eclip_authentication_requests')) {
            return;
        }

        $requestIds = DB::table('eclip_authentication_requests')
            ->where('status', 'pending')
            ->whereNull('started_at')
            ->whereNull('decided_at')
            ->where('remarks', self::BACKFILL_REMARKS)
            ->pluck('id');

        if ($requestIds->isEmpty()) {
            return;
        }

        DB::table('eclip_authentication_histories')->whereIn('authentication_request_id', $requestIds)->delete();
        DB::table('eclip_authentication_requests')->whereIn('id', $requestIds)->delete();
    }
};
