<?php

declare(strict_types=1);

namespace App\Domain\Unit\Enums;

enum RoleType: string
{
    case ATTACK = 'Attack';
    case BALANCE = 'Balance';
    case DEFENSE = 'Defense';
    case SUPPORT = 'Support';
    case UNIQUE = 'Unique';
    case NONE = 'None';
    case TECHNICAL = 'Technical';
    case SPECIAL = 'Special';
}
