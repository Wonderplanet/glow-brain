<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum QuestDifficulty: string
{
    case NORMAL = 'Normal';
    case HARD = 'Hard';
    case EXTRA = 'Extra';
}
