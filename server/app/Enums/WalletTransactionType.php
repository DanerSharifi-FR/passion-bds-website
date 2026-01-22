<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletTransactionType: string
{
    case INITIAL = 'INITIAL';
    case BET_PLACE = 'BET_PLACE';
    case BET_UPDATE_DIFF = 'BET_UPDATE_DIFF';
    case BET_CANCEL_REFUND = 'BET_CANCEL_REFUND';
    case PAYOUT = 'PAYOUT';
    case ADJUSTMENT = 'ADJUSTMENT';
    case MATCH_DELETED_REFUND = 'MATCH_DELETED_REFUND';
}
