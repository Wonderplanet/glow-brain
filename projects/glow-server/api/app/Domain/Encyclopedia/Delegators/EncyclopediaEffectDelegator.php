<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Delegators;

use App\Domain\Encyclopedia\Repositories\UsrReceivedUnitEncyclopediaRewardRepository;
use App\Domain\Encyclopedia\Services\EncyclopediaEffectService;
use App\Domain\Resource\Entities\EncyclopediaEffect;
use Illuminate\Support\Collection;

class EncyclopediaEffectDelegator
{
    public function __construct(
        private EncyclopediaEffectService $encyclopediaEffectService,
        private UsrReceivedUnitEncyclopediaRewardRepository $usrReceivedUnitEncyclopediaRewardRepository,
    ) {
    }

    public function getMstUnitEncyclopediaEffectsByGrade(int $unitTotalGrade): Collection
    {
        return $this->encyclopediaEffectService->getMstUnitEncyclopediaEffectsByGrade($unitTotalGrade);
    }

    /**
     * 指定したユーザーの図鑑効果リストを取得する
     *
     * @param string $usrUserId
     * @return Collection
     */
    public function getUserEncyclopediaEffects(string $usrUserId): Collection
    {
        return $this->usrReceivedUnitEncyclopediaRewardRepository->getList($usrUserId);
    }

    public function getEncyclopediaEffectDataByIds(
        Collection $mstUnitEncyclopediaEffectIds
    ): EncyclopediaEffect {
        return $this->encyclopediaEffectService->getEncyclopediaEffectDataByIds($mstUnitEncyclopediaEffectIds);
    }
}
