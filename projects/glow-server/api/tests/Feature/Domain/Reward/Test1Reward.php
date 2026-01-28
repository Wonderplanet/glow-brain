<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardType;

class Test1Reward extends BaseReward
{
    private string $test1Id;

    public function __construct(
        string|RewardType $type,
        ?string $resourceId,
        int $amount,
        string $test1Id = '',
    ) {
        parent::__construct(
            is_string($type) ? $type : $type->value,
            $resourceId,
            $amount,
            new LogTriggerDto('Test1Reward', $test1Id),
        );

        $this->test1Id = $test1Id;
    }

    public function getTestId(): string
    {
        return $this->test1Id;
    }
}
