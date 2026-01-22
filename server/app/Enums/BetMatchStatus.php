<?php

declare(strict_types=1);

namespace App\Enums;

enum BetMatchStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case LOCKED = 'LOCKED';
    case FINISHED = 'FINISHED';
    case CANCELLED = 'CANCELLED';
}
