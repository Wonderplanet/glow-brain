<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Resource\Enums\InGameContentType as ApiInGameContentType;

enum InGameContentType: string
{
    case STAGE = ApiInGameContentType::STAGE->value;
    case ADVENT_BATTLE = ApiInGameContentType::ADVENT_BATTLE->value;
    case PVP = ApiInGameContentType::PVP->value;
}
