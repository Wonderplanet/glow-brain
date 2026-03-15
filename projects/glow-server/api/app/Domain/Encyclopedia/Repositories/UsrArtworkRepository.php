<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository as BaseRepository;
use Illuminate\Support\Collection;

class UsrArtworkRepository extends BaseRepository
{
    protected string $modelClass = UsrArtwork::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrArtwork $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_artwork_id' => $model->getMstArtworkId(),
                'is_new_encyclopedia' => $model->getIsNewEncyclopedia(),
                'grade_level' => $model->getGradeLevel(),
            ];
        })->toArray();

        UsrArtwork::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_artwork_id'],
        );
    }

    public function make(string $usrUserId, string $mstArtworkId): UsrArtworkInterface
    {
        $model = new UsrArtwork();

        $model->usr_user_id = $usrUserId;
        $model->mst_artwork_id = $mstArtworkId;
        $model->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NEW->value;
        $model->grade_level = 1;

        return $model;
    }

    public function create(string $usrUserId, string $mstArtworkId): UsrArtworkInterface
    {
        $model = $this->make($usrUserId, $mstArtworkId);
        $this->syncModel($model);

        return $model;
    }

    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function getByMstArtworkIds(string $usrUserId, Collection $mstArtworkIds): Collection
    {
        if ($mstArtworkIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstArtworkIds) {
                return $cache->filter(function (UsrArtworkInterface $model) use ($mstArtworkIds) {
                    return $mstArtworkIds->contains($model->getMstArtworkId());
                });
            },
            expectedCount: $mstArtworkIds->count(),
            dbCallback: function () use ($usrUserId, $mstArtworkIds) {
                return UsrArtwork::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_artwork_id', $mstArtworkIds)
                    ->get();
            },
        );
    }

    public function getByMstArtworkId(
        string $usrUserId,
        string $mstArtworkId,
    ): ?UsrArtworkInterface {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_artwork_id',
            $mstArtworkId,
            function () use ($usrUserId, $mstArtworkId) {
                return UsrArtwork::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_artwork_id', $mstArtworkId)
                    ->first();
            },
        );
    }
}
