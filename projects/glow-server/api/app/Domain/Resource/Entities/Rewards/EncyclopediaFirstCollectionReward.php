<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class EncyclopediaFirstCollectionReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $encyclopediaType,
        string $encycplopediaId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD->value,
                $encyclopediaType . ':' . $encycplopediaId,
            ),
        );
    }
}
