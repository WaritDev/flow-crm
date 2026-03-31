<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityStreamUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $eventType,
        public ?string $activityId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('sales.user.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'activity.stream.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->eventType,
            'activity_id' => $this->activityId,
            'occurred_at' => now()->toIso8601String(),
        ];
    }
}

