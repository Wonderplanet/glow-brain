<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum QuestType: string
{
    case NORMAL = 'Normal';
    case EVENT = 'Event';
    case ENHANCE = 'Enhance';
    case TUTORIAL = 'Tutorial';
}
