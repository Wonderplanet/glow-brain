<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitGradeUpEntity;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstUnitGradeUpRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<MstUnitGradeUpEntity>
     * @throws GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstUnitGradeUp::class);
    }

    /**
     * ラベルとグレードからmst_unit_grade_upを取得
     * @param string $unitLabel
     * @param int    $gradeLevel
     * @param bool   $isThrowError
     * @return MstUnitGradeUpEntity|null
     * @throws GameException
     */
    public function getByUnitLabelAndGradeLevel(
        string $unitLabel,
        int $gradeLevel,
        bool $isThrowError = false
    ): ?MstUnitGradeUpEntity {
        $entities = $this->getAll()->filter(function ($entity) use ($unitLabel, $gradeLevel) {
            /** @var MstUnitGradeUpEntity $entity */
            return $entity->getUnitLabel() === $unitLabel && $entity->getGradeLevel() === $gradeLevel;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_grade_up record is not found. (unit_label: $unitLabel, grade_level: $gradeLevel)",
            );
        }

        return $entities->first();
    }
}
