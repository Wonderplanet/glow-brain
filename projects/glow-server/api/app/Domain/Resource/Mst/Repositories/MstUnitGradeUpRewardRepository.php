<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitGradeUpRewardEntity;
use App\Domain\Resource\Mst\Models\MstUnitGradeUpReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstUnitGradeUpRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @param string $mstUnitId
     * @param int $fromGradeLevel
     * @param int $toGradeLevel
     * @return Collection<MstUnitGradeUpRewardEntity>
     * @throws GameException
     */
    public function getByMstUnitIdAndGradeLevel(string $mstUnitId, int $fromGradeLevel, int $toGradeLevel): Collection
    {
        return $this->masterRepository
            ->getByColumn(MstUnitGradeUpReward::class, 'mst_unit_id', $mstUnitId)
            ->filter(function (MstUnitGradeUpRewardEntity $entity) use ($fromGradeLevel, $toGradeLevel) {
                $gradeLevel = $entity->getGradeLevel();
                return $gradeLevel > $fromGradeLevel && $gradeLevel <= $toGradeLevel;
            });
    }
}
