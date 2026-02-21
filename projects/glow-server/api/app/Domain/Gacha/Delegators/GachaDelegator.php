<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Delegators;

use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Gacha\Services\GachaTutorialService;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class GachaDelegator
{
    public function __construct(
        private GachaService $gachaService,
        private GachaTutorialService $gachaTutorialService,
    ) {
    }

    public function drawTutorial(
        string $usrUserId,
        CarbonImmutable $now,
        OprGachaEntity $oprGacha,
        int $playNum,
        CostType $costType,
    ): GachaResultData {
        return $this->gachaTutorialService->draw(
            $usrUserId,
            $now,
            $oprGacha,
            $playNum,
            $costType,
        );
    }

    /**
     * @param array<string> $prizeTypes
     * @return Collection<GachaReward>
     */
    public function makeGachaRewardByGachaBoxes(
        Collection $gachaBoxes,
        string $oprGachaId,
        array $prizeTypes = [],
    ): Collection {
        return $this->gachaService->makeGachaRewardByGachaBoxes($gachaBoxes, $oprGachaId, $prizeTypes);
    }

    /**
     * チュートリアル完了ガチャを解放する
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function unlockTutorialCompleteGacha(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        $this->gachaTutorialService->unlockMainPartTutorialCompleteGacha($usrUserId, $now);
    }

    public function addGachaHistory(
        string $usrUserId,
        string $oprGachaId,
        string $costType,
        ?string $costId,
        int $costNum,
        int $playNum,
        CarbonImmutable $now,
        Collection $gachaRewards
    ): void {
        $this->gachaService->addGachaHistory(
            $usrUserId,
            $oprGachaId,
            $costType,
            $costId,
            $costNum,
            $playNum,
            $now,
            $gachaRewards
        );
    }
}
