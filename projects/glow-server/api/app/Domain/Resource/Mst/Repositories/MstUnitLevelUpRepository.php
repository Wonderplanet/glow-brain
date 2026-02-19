<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitLevelUpEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitLevelUpRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    private function getByUnitLabel(string $unitLabel): Collection
    {
        return $this->masterRepository->getByColumn(
            MstUnitLevelUp::class,
            'unit_label',
            $unitLabel,
            function ($entities) {
                return $entities->keyBy->getLevel();
            }
        );
    }

    /**
     * 特定範囲のレベルアップ情報を取得する
     * @param string $unitLabel
     * @param int    $fromLevel
     * @param int    $toLevel
     * @return Collection<Entity>
     */
    public function getLevelIsInRange(string $unitLabel, int $fromLevel, int $toLevel): Collection
    {
        return $this->getByUnitLabel($unitLabel)->only(range($fromLevel, $toLevel));
    }

    /**
     * 特定ラベルの最大レベルアップ情報を取得する
     * @param string $unitLabel
     * @param bool   $isThrowError
     * @return Entity
     * @throws GameException
     */
    public function getMaxMstUnitLevelUp(string $unitLabel, bool $isThrowError = false): Entity
    {
        $entities = $this->getByUnitLabel($unitLabel);
        $maxLevel = $entities->keys()->max();
        $entities = $entities->only($maxLevel);

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_level_ups record is not found. (unitLabel: $unitLabel)"
            );
        }

        return $entities->first();
    }

    /**
     * 特定ラベルの最大レベルアップ情報を取得する
     * @param Collection<string> $unitLabels
     * @param bool   $isThrowError
     * @return Collection<Entity>
     * @throws GameException
     */
    public function getMaxMstUnitLevelUps(Collection $unitLabels, bool $isThrowError = false): Collection
    {
        return $unitLabels->unique()
            ->mapWithKeys(function ($unitLabel) use ($isThrowError) {
                return [$unitLabel => $this->getMaxMstUnitLevelUp($unitLabel, $isThrowError)];
            });
    }
}
