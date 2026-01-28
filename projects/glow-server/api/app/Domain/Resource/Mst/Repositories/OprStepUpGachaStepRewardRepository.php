<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprStepUpGachaStepRewardEntity;
use App\Domain\Resource\Mst\Models\OprStepUpGachaStepReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprStepUpGachaStepRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository
    ) {
    }

    /**
     * opr_gacha_idでフィルタリングされたおまけ報酬情報を取得
     *
     * @param string $oprGachaId
     * @return Collection<OprStepUpGachaStepRewardEntity>
     * @throws GameException
     */
    private function getByOprGachaId(string $oprGachaId): Collection
    {
        return $this->masterRepository->getByColumn(OprStepUpGachaStepReward::class, 'opr_gacha_id', $oprGachaId);
    }

    /**
     * 指定されたガシャID、ステップ番号、ループ回数に該当するおまけ報酬を取得
     *
     * @param string $oprGachaId
     * @param int $stepNumber
     * @param int $loopCount
     * @return Collection<OprStepUpGachaStepRewardEntity>
     * @throws GameException
     */
    public function getRewardsForStep(string $oprGachaId, int $stepNumber, int $loopCount): Collection
    {
        return $this->getByOprGachaId($oprGachaId)->filter(
            function (OprStepUpGachaStepRewardEntity $entity) use ($stepNumber, $loopCount) {
                // stepNumberが一致し、かつloop_count_targetがNULLまたは指定のループ回数と一致するもの
                return $entity->getStepNumber() === $stepNumber
                && ($entity->getLoopCountTarget() === null || $entity->getLoopCountTarget() === $loopCount);
            }
        );
    }
}
