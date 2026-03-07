<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitGradeCoefficientEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient as Model;
use App\Domain\Unit\Constants\UnitConstant;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitGradeCoefficientRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity> key: {unit_label}_{grade_level}, value: Entity
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(
            Model::class,
            function (Collection $entities) {
                return $entities->mapWithKeys(function ($entity) {
                    /** @var Entity $entity */
                    return [$entity->makeUnitLabelAndGradeLevelKey() => $entity];
                });
            }
        );
    }

    public function getByUnitLabelAndGradeLevel(
        string $unitLabel,
        int $gradeLevel,
        bool $isThrowError = false
    ): ?Entity {
        $entities = $this->getAll()->only(Entity::makeUnitLabelAndGradeLevelKeyStatic($unitLabel, $gradeLevel));

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_grade_coefficients record is not found."
                . " (unit_label: $unitLabel, grade_level: $gradeLevel)",
            );
        }

        return $entities->first();
    }

    /**
     * unit_label と grade_level の組み合わせ を複数指定して取得する
     *
     * @param Collection<array{string, int}> $unitLabelGradeLevelPairs [0]: unit_label, [1]: grade_level
     * @return Collection<string, Entity> key: unitLabelAndGradeLevelKey, value: Entity
     */
    public function getByUnitLabelsAndGradeLevels(
        Collection $unitLabelGradeLevelPairs,
        bool $isThrowError = false
    ): Collection {
        $targetKeys = collect();
        foreach ($unitLabelGradeLevelPairs as $unitLabelGradeLevelPair) {
            [$unitLabel, $gradeLevel] = $unitLabelGradeLevelPair;
            $targetKeys->push(Entity::makeUnitLabelAndGradeLevelKeyStatic($unitLabel, (int)$gradeLevel));
        }

        $entities = $this->getAll()->only($targetKeys);

        if ($isThrowError && $entities->isEmpty()) {
            $pairs = $targetKeys->values()->join(', ');

            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_grade_coefficients records are not found."
                . " (unit_label and grade_level pairs: $pairs)"
            );
        }

        return $entities;
    }

    public function getCoefficientByUnitLabelAndGradeLevel(
        string $unitLabel,
        int $gradeLevel,
        bool $isThrowError = false
    ): int {
        $entity = $this->getByUnitLabelAndGradeLevel($unitLabel, $gradeLevel, $isThrowError);
        return $entity ? $entity->getCoefficient() : UnitConstant::DEFAULT_UNIT_GRADE_COEFFICIENT;
    }
}
