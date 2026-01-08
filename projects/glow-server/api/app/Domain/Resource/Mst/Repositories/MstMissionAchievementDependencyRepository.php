<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstMissionAchievementDependencyEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionAchievementDependency as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionAchievementDependencyRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Collection<Entity>> key: mst_mission_achievement_id, value: Collection<Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(
            Model::class,
            function ($entities) {
                return $entities->groupBy->getMstMissionId();
            }
        );
    }

    public function getSameGroupsByMstMissionIds(Collection $mstMissionIds): Collection
    {
        $targetMstMissionIds = $mstMissionIds->unique()
            ->mapWithKeys(function ($mstMissionId) {
                return [$mstMissionId => true];
            });

        $all = $this->getAll();
        $groupIds = collect();
        foreach ($all as $mstMissionId => $entities) {
            if (!$targetMstMissionIds->has($mstMissionId)) {
                continue;
            }
            foreach ($entities as $entity) {
                $groupIds->put($entity->getGroupId(), true);
            }
        }
        $all = $all->flatten();

        $result = collect();
        foreach ($all as $entity) {
            if ($groupIds->has($entity->getGroupId())) {
                $result->push($entity);
            }
        }

        return $result;
    }
}
