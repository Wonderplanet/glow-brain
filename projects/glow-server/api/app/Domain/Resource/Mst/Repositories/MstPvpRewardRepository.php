<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstPvpRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvpReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $mstPvpRewardGroupId
     * @param bool $isThrowError 存在しない場合に例外を投げるかどうか
     * @return Collection<Entity>
     * @throws GameException
     */
    public function getByGroupId(
        string $mstPvpRewardGroupId,
        bool $isThrowError = false,
    ): Collection {
        $entities = $this->getAll()->filter(function (Entity $entity) use ($mstPvpRewardGroupId) {
            return $entity->getMstPvpRewardGroupId() === $mstPvpRewardGroupId;
        });

        if ($entities->isEmpty() && $isThrowError) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_rewards record is not found. (group_id: %s)',
                    $mstPvpRewardGroupId
                ),
            );
        }

        return $entities;
    }

    /**
     * @param Collection $mstPvpRewardGroupIds
     * @return Collection<Entity>
     */
    public function getByGroupIds(
        Collection $mstPvpRewardGroupIds,
    ): Collection {
        return $this->getAll()
            ->filter(fn(Entity $entity) => $mstPvpRewardGroupIds->contains($entity->getMstPvpRewardGroupId()))
            ->values();
    }
}
