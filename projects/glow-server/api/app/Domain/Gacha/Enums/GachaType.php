<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum GachaType: string
{
    case NORMAL = 'Normal';
    case PREMIUM = 'Premium';
    case PICKUP = 'Pickup';
    case FREE = 'Free';
    case TICKET = 'Ticket';
    case FESTIVAL = 'Festival';
    case PAID_ONLY = 'PaidOnly';
    case MEDAL = 'Medal';
    case TUTORIAL = 'Tutorial';
}
