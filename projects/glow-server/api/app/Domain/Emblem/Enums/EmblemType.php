<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Enums;

enum EmblemType: string
{
    case SERIES = 'Series';
    case EVENT = 'Event';
}
