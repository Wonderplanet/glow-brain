<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ShopDiamondReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::SHOP_DIAMOND_REWARD->value,
                $resourceId,
            ),
        );
    }
}
