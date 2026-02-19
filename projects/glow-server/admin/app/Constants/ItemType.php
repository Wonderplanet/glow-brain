<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Item\Enums\ItemType as ApiItemType;

enum ItemType: string
{
    case RANDOM_FRAGMENT_BOX = ApiItemType::RANDOM_FRAGMENT_BOX->value;
    case SELECTION_FRAGMENT_BOX = ApiItemType::SELECTION_FRAGMENT_BOX->value;
    case RANK_UP_MATERIAL = ApiItemType::RANK_UP_MATERIAL->value;
    case CHARACTER_FRAGMENT = ApiItemType::CHARACTER_FRAGMENT->value;
}
