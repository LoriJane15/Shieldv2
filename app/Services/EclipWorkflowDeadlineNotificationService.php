<?php

namespace App\Services;

use App\Models\EclipWorkflowActivity;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Support\Collection;

class EclipWorkflowDeadlineNotificationService
{
    public function sendUpcoming(EclipWorkflowActivity $activity): int
    {
        return $this->send($activity, 'upcoming', "E-CLIP Step {$activity->step_code} is due by {$activity->due_at?->format('M d, Y')}. Please complete the required action.");
    }

    public function sendOverdue(EclipWorkflowActivity $activity): int
    {
        return $this->send($activity, 'overdue', "E-CLIP Step {$activity->step_code} is overdue. Please record the required action or return for correction.");
    }

    private function send(EclipWorkflowActivity $activity, string $kind, string $message): int
    {
        $activity->loadMissing('eclipCase', 'histories');
        $key = $kind.'|'.$activity->due_at?->toDateString();
        $sent = 0;

        foreach ($this->recipients($activity) as $recipient) {
            if ($activity->histories->contains(fn ($history) => data_get($history->data, 'deadline_notification_key') === $key && data_get($history->data, 'recipient_id') === $recipient->id)) {
                continue;
            }

            $recipient->notify(new EclipCaseActionNotification($activity->eclipCase, $message, $this->routeFor($recipient)));
            $activity->histories()->create([
                'to_status' => $activity->status,
                'remarks' => ucfirst($kind).' deadline notification sent.',
                'data' => ['deadline_notification_key' => $key, 'recipient_id' => $recipient->id],
            ]);
            $sent++;
        }

        return $sent;
    }

    private function recipients(EclipWorkflowActivity $activity): Collection
    {
        $case = $activity->eclipCase;
        $roles = $activity->responsible_roles ?? [];
        $participantRoles = ['lswdo', 'japic', 'pnp', 'afp'];
        $participantRecipients = $case->participants()
            ->wherePivot('is_active', true)
            ->whereIn('users.role', array_values(array_intersect($roles, $participantRoles)))
            ->where('users.is_active', true)
            ->get();
        $unassignedRoles = array_values(array_diff($roles, $participantRoles));
        if ($unassignedRoles === []) {
            return $participantRecipients;
        }

        $scopedRoles = ['dilg_provincial_focal', 'local_eclip_committee'];
        $officeRecipients = User::query()->whereIn('role', $unassignedRoles)->where('is_active', true)
            ->where(function ($query) use ($case, $unassignedRoles, $scopedRoles) {
                $query->whereIn('role', array_values(array_diff($unassignedRoles, $scopedRoles)));
                if (array_intersect($unassignedRoles, $scopedRoles) !== []) {
                    $query->orWhere(fn ($scoped) => $scoped->whereIn('role', $scopedRoles)->where('municipality_id', $case->municipality_id));
                }
            })->get();

        return $participantRecipients->merge($officeRecipients)->unique('id')->values();
    }

    private function routeFor(User $user): string
    {
        return match ($user->role) {
            'lswdo' => 'lswdo.eclip.show',
            'japic' => 'japic.eclip.show',
            'pnp', 'afp' => $user->role.'.eclip-fea.show',
            'dilg_fms' => 'eclip_funding.cases.show',
            'local_eclip_committee' => 'local_eclip.cases.show',
            default => 'dilg_reviewer.cases.show',
        };
    }
}
