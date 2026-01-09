<?php

declare(strict_types=1);

namespace App\Domain\Unit\Enums;

enum AttackKind: string
{
    case NORMAL = 'Normal';
    case SPECIAL = 'Special';
    case APPEARANCE = 'Appearance';
}
