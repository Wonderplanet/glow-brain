<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

/**
 * ステップアップガシャのおまけ報酬
 */
class StepupGachaStepReward extends BaseReward
{
    private string $oprGachaId;
    private int $stepNumber;
    private int $loopCount;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $oprGachaId,
        int $stepNumber,
        int $loopCount,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::GACHA_REWARD->value,
                $oprGachaId,
                "step_{$stepNumber}_loop_{$loopCount}",
            ),
        );

        $this->oprGachaId = $oprGachaId;
        $this->stepNumber = $stepNumber;
        $this->loopCount = $loopCount;
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getStepNumber(): int
    {
        return $this->stepNumber;
    }

    public function getLoopCount(): int
    {
        return $this->loopCount;
    }
}
