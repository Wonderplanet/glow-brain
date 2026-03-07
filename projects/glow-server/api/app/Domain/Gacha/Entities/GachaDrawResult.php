<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use Illuminate\Support\Collection;

/**
 * ガチャ抽選結果のエンティティ
 */
class GachaDrawResult
{
    /**
     * @param Collection<int, GachaReward> $gachaRewards
     * @param UsrGachaInterface $usrGacha
     * @param Collection<int, UsrGachaUpperInterface> $usrGachaUppers
     * @param ILogGachaAction $logGachaAction
     * @param ?int $currentStepNumber
     * @param ?int $loopCount
     */
    public function __construct(
        private Collection $gachaRewards,
        private UsrGachaInterface $usrGacha,
        private Collection $usrGachaUppers,
        private ILogGachaAction $logGachaAction,
        private ?int $currentStepNumber = null,
        private ?int $loopCount = null,
    ) {
    }

    /**
     * @return Collection<int, GachaReward>
     */
    public function getGachaRewards(): Collection
    {
        return $this->gachaRewards;
    }

    public function getUsrGacha(): UsrGachaInterface
    {
        return $this->usrGacha;
    }

    /**
     * @return Collection<int, UsrGachaUpperInterface>
     */
    public function getUsrGachaUppers(): Collection
    {
        return $this->usrGachaUppers;
    }

    public function getLogGachaAction(): ILogGachaAction
    {
        return $this->logGachaAction;
    }

    public function getCurrentStepNumber(): ?int
    {
        return $this->currentStepNumber;
    }

    public function getLoopCount(): ?int
    {
        return $this->loopCount;
    }
}
