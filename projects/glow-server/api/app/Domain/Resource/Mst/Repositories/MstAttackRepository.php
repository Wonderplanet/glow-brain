<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstAttackEntity as Entity;
use App\Domain\Resource\Mst\Models\MstAttack as Model;
use App\Domain\Unit\Enums\AttackKind;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstAttackRepository
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
     * @param string $mstUnitId
     * @param int $unitGrade
     * @return ?Entity
     */
    public function getSpecialAttack(string $mstUnitId, int $unitGrade): ?Entity
    {
        return $this->getAll()->filter(function ($entity) use ($mstUnitId, $unitGrade) {
            /** @var Entity $entity */
            return $entity->getMstUnitId() === $mstUnitId &&
                $entity->getAttackKind() === AttackKind::SPECIAL->value &&
                $entity->getUnitGrade() === $unitGrade;
        })->first();
    }

    /**
     * @param Collection<string> $mstUnitIds
     * @return Collection<Collection<?Entity>>
     */
    public function getNormalAttacks(Collection $mstUnitIds): Collection
    {
        $allEntities = $this->getAll();
        $mstAttacks = collect();
        foreach ($mstUnitIds as $mstUnitId) {
            $attack = $allEntities->filter(function ($entity) use ($mstUnitId) {
                /** @var Entity $entity */
                return $entity->getMstUnitId() === $mstUnitId &&
                    $entity->getAttackKind() === AttackKind::NORMAL->value;
            })->first();
            $mstAttacks->put($mstUnitId, $attack);
        }

        return $mstAttacks;
    }

    /**
     * @param Collection<string, int> $mstUnitIdGradeMap キー: mst_unit_id, 値: unit_grade
     * @return Collection<Collection<?Entity>>
     */
    public function getSpecialAttacks(Collection $mstUnitIdGradeMap): Collection
    {
        $allEntities = $this->getAll();
        $mstAttacks = collect();
        foreach ($allEntities as $entity) {
            /** @var Entity $entity */
            if ($entity->getAttackKind() !== AttackKind::SPECIAL->value) {
                // Specialではないので対象外
                continue;
            }

            $mstUnitId = $entity->getMstUnitId();
            $unitGrade = $entity->getUnitGrade();
            $targetGrade = $mstUnitIdGradeMap->get($mstUnitId);
            if ($targetGrade !== $unitGrade) {
                // グレードが違うので対象外
                continue;
            }
            if (!$mstAttacks->has($mstUnitId)) {
                $mstAttacks->put($mstUnitId, collect());
            }
            $mstAttacks[$mstUnitId]->put($unitGrade, $entity);
        }

        return $mstAttacks;
    }
}
