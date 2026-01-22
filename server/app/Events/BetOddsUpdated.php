<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetOddsUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param int $matchId
     * @param array<int, array{id:int,label:string,current_odds:float,pool_total:int}> $options
     */
    public function __construct(
        public int $matchId,
        public array $options,
    ) {
    }

    /**
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('bet.match.' . $this->matchId);
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'matchId' => $this->matchId,
            'options' => $this->options,
        ];
    }
}
