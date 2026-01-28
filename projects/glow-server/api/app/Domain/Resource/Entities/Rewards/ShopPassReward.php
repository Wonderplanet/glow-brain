<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ShopPassReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        private string $mstShopPassId,
        private string $passRewardType,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::SHOP_PASS_REWARD->value,
                $mstShopPassId,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        $base = parent::formatToResponse();
        $base['mstShopPassId'] = $this->mstShopPassId;
        $base['passRewardType'] = $this->passRewardType;
        return $base;
    }
}
