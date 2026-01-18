<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Tutorial\Models\UsrTutorial;
use App\Domain\Tutorial\Models\UsrTutorialInterface;
use Illuminate\Support\Collection;

class UsrTutorialRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrTutorial::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrTutorial $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_tutorial_id' => $model->getMstTutorialId(),
            ];
        })->toArray();

        UsrTutorial::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_tutorial_id'],
        );
    }

    public function create(string $usrUserId, string $mstTutorialId): UsrTutorialInterface
    {
        $model = new UsrTutorial();

        $model->usr_user_id = $usrUserId;
        $model->mst_tutorial_id = $mstTutorialId;

        $this->syncModel($model);

        return $model;
    }

    public function getByMstTutorialId(string $usrUserId, string $mstTutorialId): ?UsrTutorialInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_tutorial_id',
            $mstTutorialId,
            function () use ($usrUserId, $mstTutorialId) {
                return UsrTutorial::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_tutorial_id', $mstTutorialId)
                    ->first();
            },
        );
    }

    public function getOrCreate(string $usrUserId, string $mstTutorialId): UsrTutorialInterface
    {
        $model = $this->getByMstTutorialId($usrUserId, $mstTutorialId);
        if ($model === null) {
            $model = $this->create($usrUserId, $mstTutorialId);
        }

        return $model;
    }

    /**
     * @param Collection<string> $mstTutorialIds
     * @return Collection<UsrTutorialInterface>
     */
    public function getByMstTutorialIds(string $usrUserId, Collection $mstTutorialIds): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstTutorialIds) {
                return $cache->filter(function (UsrTutorialInterface $model) use ($mstTutorialIds) {
                    return $mstTutorialIds->contains($model->getMstTutorialId());
                });
            },
            expectedCount: $mstTutorialIds->count(),
            dbCallback: function () use ($usrUserId, $mstTutorialIds) {
                return UsrTutorial::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_tutorial_id', $mstTutorialIds)
                    ->get();
            },
        );
    }
}
