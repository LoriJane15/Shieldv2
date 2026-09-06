<?php

namespace App\Events;

use App\Models\RcspFileComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Live delivery of a comment on an RCSP form, so the Katuparan Center and the
 * LGU see each other's remarks without reloading.
 *
 * Replaces the legacy Ratchet server (`websocket_server.php`), which kept one
 * room per form id and broadcast `new_comment` to everyone in it. The room is
 * now the private channel `rcsp-form.{id}`; the comment is still persisted by
 * the controller before this fires, exactly as the legacy server did.
 */
class RcspCommentPosted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public RcspFileComment $comment)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("rcsp-form.{$this->comment->rcsp_form_id}");
    }

    public function broadcastAs(): string
    {
        return 'comment.posted';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $user = $this->comment->user;

        return [
            'id' => $this->comment->id,
            'form_id' => $this->comment->rcsp_form_id,
            'phase_id' => $this->comment->rcsp_phase_id,
            'activity_id' => $this->comment->rcsp_activity_id,
            'text' => $this->comment->text,
            'user_id' => $this->comment->user_id,
            'user_name' => $user?->name ?: $user?->username,
            'user_role' => $user?->role,
            'user_logo' => $user?->logo ? asset('assets/'.$user->logo) : asset('assets/img/kc-logo.svg'),
            'at' => $this->comment->created_at?->diffForHumans(),
        ];
    }
}
