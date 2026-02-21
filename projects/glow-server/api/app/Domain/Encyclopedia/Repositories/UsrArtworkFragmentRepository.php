<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository as BaseRepository;
use Illuminate\Support\Collection;

class UsrArtworkFragmentRepository extends BaseRepository
{
    protected string $modelClass = UsrArtworkFragment::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrArtworkFragment $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_artwork_id' => $model->getMstArtworkId(),
                'mst_artwork_fragment_id' => $model->getMstArtworkFragmentId(),
            ];
        })->toArray();

        UsrArtworkFragment::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_artwork_fragment_id'],
        );
    }

    public function make(
        string $usrUserId,
        string $mstArtworkId,
        string $mstArtworkFragmentId
    ): UsrArtworkFragmentInterface {
        $model = new UsrArtworkFragment();

        $model->usr_user_id = $usrUserId;
        $model->mst_artwork_id = $mstArtworkId;
        $model->mst_artwork_fragment_id = $mstArtworkFragmentId;

        return $model;
    }

    public function create(
        string $usrUserId,
        string $mstArtworkId,
        string $mstArtworkFragmentId
    ): UsrArtworkFragmentInterface {
        $model = $this->make($usrUserId, $mstArtworkId, $mstArtworkFragmentId);
        $this->syncModel($model);

        return $model;
    }

    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    /**
     * @return Collection<string, UsrArtworkFragmentInterface> key: mst_artwork_fragments.id
     */
    public function getByMstArtworkFragmentIds(string $usrUserId, Collection $mstArtworkFragmentIds): Collection
    {
        if ($mstArtworkFragmentIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstArtworkFragmentIds) {
                return $cache->filter(function (UsrArtworkFragmentInterface $model) use ($mstArtworkFragmentIds) {
                    return $mstArtworkFragmentIds->contains($model->getMstArtworkFragmentId());
                });
            },
            expectedCount: $mstArtworkFragmentIds->count(),
            dbCallback: function () use ($usrUserId, $mstArtworkFragmentIds) {
                return UsrArtworkFragment::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_artwork_fragment_id', $mstArtworkFragmentIds)
                    ->get();
            },
        )->keyBy(function (UsrArtworkFragmentInterface $model) {
            return $model->getMstArtworkFragmentId();
        });
    }

    public function getByMstArtworkIds(string $usrUserId, Collection $mstArtworkIds): Collection
    {
        if ($mstArtworkIds->isEmpty()) {
            return collect();
        }

        // TODO 検索条件のIDとデータが1:多の場合にキャッシュから取得する関数が現状なさそうなので後で修正したい
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstArtworkIds) {
                return $cache->filter(function (UsrArtworkFragmentInterface $model) use ($mstArtworkIds) {
                    return $mstArtworkIds->contains($model->getMstArtworkId());
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId, $mstArtworkIds) {
                return UsrArtworkFragment::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_artwork_id', $mstArtworkIds)
                    ->get();
            },
        );
    }
}
