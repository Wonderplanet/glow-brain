<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class GachaReward extends BaseReward
{
    private string $oprGachaId;
    private int $sortOrder;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $oprGachaId,
        int $order,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::GACHA_REWARD->value,
                $oprGachaId,
                (string) $order,
            ),
        );

        $this->sortOrder = $order;
        $this->oprGachaId = $oprGachaId;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }
}
