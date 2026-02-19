<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum RewardConvertedReason: string
{
    case DUPLICATED_EMBLEM = 'DuplicatedEmblem';
    case DUPLICATED_UNIT = 'DuplicatedUnit';
    case DUPLICATED_ARTWORK = 'DuplicatedArtwork';
    case CONVERT_IDLE_BOX = 'ConvertIdleBox';
}
