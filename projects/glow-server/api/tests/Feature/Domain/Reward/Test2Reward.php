<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;

class Test2Reward extends BaseReward
{
    private string $test2Id;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $test2Id,
    ) {
        parent::__construct($type, $resourceId, $amount, new LogTriggerDto('Test2Reward', $test2Id));

        $this->test2Id = $test2Id;
    }

    public function getTestId(): string
    {
        return $this->test2Id;
    }
}
