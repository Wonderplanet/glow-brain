<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstStageEnhanceRewardParamEntity as Entity;
use App\Domain\Resource\Mst\Models\MstStageEnhanceRewardParam as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstStageEnhanceRewardParamRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_stage_enhance_reward_params record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    /**
     * 指定したスコア以下のmin_threshold_scoreを持つデータの内で最大のmin_threshold_scoreを持つデータを取得する
     *
     * @param int $score
     */
    public function getByMinThresholdScoreUnder(int $score): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($score) {
            return $entity->getMinThresholdScore() <= $score;
        });

        return $entities->sortByDesc(function ($entity) {
            return $entity->getMinThresholdScore();
        })->first();
    }
}
