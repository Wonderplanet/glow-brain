<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum AppearanceCondition: string
{
    case ALWAYS = 'Always';
    case HAS_TICKET = 'HasTicket';
}
