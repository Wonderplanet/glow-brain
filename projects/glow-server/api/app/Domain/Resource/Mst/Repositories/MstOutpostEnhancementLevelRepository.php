<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstOutpostEnhancementLevelEntity;
use App\Domain\Resource\Mst\Entities\MstOutpostEnhancementLevelEntity as Entity;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstOutpostEnhancementLevelRepository
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
        return $this->getAll()->keyBy(function ($entity): string {
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
                    'mst_outpost_enhancement_levels record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    public function getByEnhancementIdAndLevel(string $id, int $level, bool $isThrowError = false): Entity
    {
        $entities = $this->getAll()->filter(function (MstOutpostEnhancementLevelEntity $entity) use ($id, $level) {
            return $entity->getMstOutpostEnhancementId() === $id && $entity->getLevel() === $level;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_outpost_enhancement_levels record is not found. (mst_outpost_enhancement_id: %s, level: %s)',
                    $id,
                    $level
                ),
            );
        }

        return $entities->first();
    }

    /**
     * 特定範囲のレベルアップ情報を取得する
     * @param string $mstOutpostEnhancementId
     * @param int    $fromLevel
     * @param int    $toLevel
     * @param bool   $isThrowError
     * @return Collection<MstOutpostEnhancementLevelEntity>
     * @throws GameException
     */
    public function getLevelIsInRange(
        string $mstOutpostEnhancementId,
        int $fromLevel,
        int $toLevel,
        bool $isThrowError = false
    ): Collection {
        $entities = $this->getAll()->filter(
            function (MstOutpostEnhancementLevelEntity $entity) use ($mstOutpostEnhancementId, $fromLevel, $toLevel) {
                /** @var MstOutpostEnhancementLevelEntity $entity */
                return $entity->getMstOutpostEnhancementId() === $mstOutpostEnhancementId &&
                    $fromLevel <= $entity->getLevel() && $entity->getLevel() <= $toLevel;
            }
        )
            ->sortBy(fn($entity) => $entity->getLevel());

        if ($isThrowError && ($entities->isEmpty() || ($entities->count() !== ($toLevel - $fromLevel + 1)))) {
            // データが空もしくはfrom-toの範囲のデータが取得できていない場合はエラー
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    "mst_outpost_enhancement_levels record is not found. (id: %s, level: %d - %d)",
                    $mstOutpostEnhancementId,
                    $fromLevel,
                    $toLevel
                )
            );
        }
        return $entities;
    }
}
