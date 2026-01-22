<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\BetBet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use UnitEnum;
use BackedEnum;

class UserBetUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public BetBet $bet,
    ) {
    }

    /**
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('bet.user.' . $this->userId);
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $status = $this->bet->status;
        if ($status instanceof BackedEnum) {
            $statusValue = $status->value;
        } elseif ($status instanceof UnitEnum) {
            $statusValue = $status->name;
        } else {
            $statusValue = (string) $status;
        }

        return [
            'userId' => $this->userId,
            'bet' => [
                'id' => (int) $this->bet->id,
                'match_id' => (int) $this->bet->match_id,
                'option_id' => (int) $this->bet->option_id,
                'stake' => (int) $this->bet->stake,
                'odds_locked' => (float) $this->bet->odds_locked,
                'status' => $statusValue,
                'editable_until' => $this->bet->editable_until?->toIso8601String(),
            ],
        ];
    }
}
