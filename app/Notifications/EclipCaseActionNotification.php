<?php

namespace App\Notifications;

use App\Models\EclipCase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EclipCaseActionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EclipCase $case,
        private readonly string $message,
        private readonly string $routeName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'case_number' => $this->case->case_number,
            'status' => $this->case->status->value,
            'message' => $this->message,
            'route' => $this->routeName,
            'case_id' => $this->case->id,
        ];
    }
}
