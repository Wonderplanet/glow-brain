<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class UserLevelUpReward extends BaseReward
{
    /**
     * @var int mst_user_level_bonuses.level
     */
    private int $userLevel;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        int $userLevel,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::USER_LEVEL_UP_REWARD->value,
                (string) $userLevel,
            ),
        );

        $this->userLevel = $userLevel;
    }

    public function getUserLevel(): int
    {
        return $this->userLevel;
    }

    public function formatToResponse(): array
    {
        return [
            'level' => $this->getUserLevel(),
            'reward' => parent::formatToResponse(),
        ];
    }
}
