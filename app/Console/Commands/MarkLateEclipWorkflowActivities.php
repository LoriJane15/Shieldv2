<?php

namespace App\Console\Commands;

use App\Models\EclipWorkflowActivity;
use App\Services\EclipWorkflowDeadlineNotificationService;
use Illuminate\Console\Command;

class MarkLateEclipWorkflowActivities extends Command
{
    protected $signature = 'eclip:mark-late-activities';

    protected $description = 'Mark overdue official E-CLIP workflow activities as late';

    public function handle(EclipWorkflowDeadlineNotificationService $notifications): int
    {
        $count = 0;
        $upcomingCount = 0;
        EclipWorkflowActivity::query()
            ->whereIn('status', ['pending', 'ongoing'])
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->chunkById(100, function ($activities) use (&$upcomingCount, $notifications) {
                foreach ($activities as $activity) {
                    $upcomingCount += $notifications->sendUpcoming($activity);
                }
            });

        EclipWorkflowActivity::query()
            ->whereIn('status', ['pending', 'ongoing'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->chunkById(100, function ($activities) use (&$count, $notifications) {
                foreach ($activities as $activity) {
                    $from = $activity->status;
                    $activity->update(['status' => 'late']);
                    $activity->histories()->create([
                        'from_status' => $from,
                        'to_status' => 'late',
                        'remarks' => 'The prescribed processing period was exceeded.',
                    ]);
                    $activity->refresh();
                    $notifications->sendOverdue($activity);
                    $count++;
                }
            });

        $this->info("Marked {$count} E-CLIP activities as late; sent {$upcomingCount} upcoming notifications.");

        return self::SUCCESS;
    }
}
