<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrEmblemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrEmblem::class;

    public function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrEmblemInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_emblem_id' => $model->getMstEmblemId(),
                'is_new_encyclopedia' => $model->getIsNewEncyclopedia(),
            ];
        })->toArray();

        UsrEmblem::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_emblem_id'],
        );
    }

    public function findByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function findByMstEmblemId(
        string $usrUserId,
        string $mstEmblemId,
        bool $isThrowError = false
    ): ?UsrEmblemInterface {
        $usrEmblem = $this->cachedGetOneWhere(
            $usrUserId,
            'mst_emblem_id',
            $mstEmblemId,
            function () use ($usrUserId, $mstEmblemId) {
                return UsrEmblem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_emblem_id', $mstEmblemId)
                    ->first();
            },
        );
        if ($isThrowError && $usrEmblem === null) {
            throw new GameException(ErrorCode::EMBLEM_NOT_OWNED, 'usr_emblem not found');
        }
        return $usrEmblem;
    }

    /**
     * @return Collection<UsrEmblemInterface>
     */
    public function findByMstEmblemIds(string $usrUserId, Collection $mstEmblemIds): Collection
    {
        if ($mstEmblemIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstEmblemIds) {
                return $cache->whereIn('mst_emblem_id', $mstEmblemIds->toArray());
            },
            expectedCount: $mstEmblemIds->count(),
            dbCallback: function () use ($usrUserId, $mstEmblemIds) {
                return UsrEmblem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_emblem_id', $mstEmblemIds)
                    ->get();
            },
        );
    }

    public function create(string $usrUserId, string $mstEmblemId): UsrEmblemInterface
    {
        $usrEmblem = new UsrEmblem();
        $usrEmblem->usr_user_id = $usrUserId;
        $usrEmblem->mst_emblem_id = $mstEmblemId;
        $usrEmblem->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NEW->value;
        return $usrEmblem;
    }

    /**
     * @param string $usrUserId
     * @param array<string> $mstEmblemIds
     * @return void
     */
    public function bulkCreate(string $usrUserId, array $mstEmblemIds): void
    {
        // エンブレムは重複所持できないので、重複を除外
        $mstEmblemIds = array_unique($mstEmblemIds);

        $usrEmblems = collect();
        foreach ($mstEmblemIds as $mstEmblemId) {
            $usrEmblems->push(
                $this->create($usrUserId, $mstEmblemId)
            );
        }

        $this->syncModels($usrEmblems);
    }
}
