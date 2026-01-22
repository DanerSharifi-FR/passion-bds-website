<?php

declare(strict_types=1);

namespace App\Enums;

enum BetBetStatus: string
{
    case ACTIVE = 'ACTIVE';
    case CANCELLED = 'CANCELLED';
    case SETTLED = 'SETTLED';
}
