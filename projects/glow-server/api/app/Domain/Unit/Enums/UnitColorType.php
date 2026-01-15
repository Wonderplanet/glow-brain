<?php

declare(strict_types=1);

namespace App\Domain\Unit\Enums;

enum UnitColorType: string
{
    case COLORLESS = 'Colorless';
    case RED = 'Red';
    case GREEN = 'Green';
    case BLUE = 'Blue';
    case YELLOW = 'Yellow';
}
