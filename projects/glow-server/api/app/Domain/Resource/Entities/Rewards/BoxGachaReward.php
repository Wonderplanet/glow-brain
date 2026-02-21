<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class BoxGachaReward extends BaseReward
{
    private string $mstBoxGachaId;
    private string $mstBoxGachaPrizeId;
    private int $sortOrder;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstBoxGachaId,
        string $mstBoxGachaPrizeId,
        int $order,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::BOX_GACHA_REWARD->value,
                $mstBoxGachaId,
                (string) $order,
            ),
        );

        $this->sortOrder = $order;
        $this->mstBoxGachaId = $mstBoxGachaId;
        $this->mstBoxGachaPrizeId = $mstBoxGachaPrizeId;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getMstBoxGachaId(): string
    {
        return $this->mstBoxGachaId;
    }

    public function getMstBoxGachaPrizeId(): string
    {
        return $this->mstBoxGachaPrizeId;
    }
}
