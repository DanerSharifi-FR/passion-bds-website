<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('bet.user.{userId}', function ($user, $userId): bool {
    return (int) $user->id === (int) $userId;
});
