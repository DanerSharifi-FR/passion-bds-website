<?php

declare(strict_types=1);

namespace App\Enums;

enum BetResult: string
{
    case WON = 'WON';
    case LOST = 'LOST';
    case REFUNDED = 'REFUNDED';
}
