<?php

declare(strict_types=1);

namespace App\Domain\Message\Enums;

enum MngMessageType: string
{
    case ALL = 'All';
    case INDIVIDUAL = 'Individual';
}
