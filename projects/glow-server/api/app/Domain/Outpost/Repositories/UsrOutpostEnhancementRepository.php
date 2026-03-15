<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Repositories;

use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Outpost\Models\UsrOutpostEnhancementInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

// 1ユーザーあたりのレコード数が、2つ以上想定される場合
class UsrOutpostEnhancementRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrOutpostEnhancement::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrOutpostEnhancement $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_outpost_id' => $model->getMstOutpostId(),
                'mst_outpost_enhancement_id' => $model->getMstOutpostEnhancementId(),
                'level' => $model->getLevel(),
            ];
        })->toArray();

        UsrOutpostEnhancement::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_outpost_id', 'mst_outpost_enhancement_id'],
            ['level'],
        );
    }

    public function create(
        string $usrUserId,
        string $mstOutpostId,
        string $mstOutpostEnhancementId
    ): UsrOutpostEnhancementInterface {
        $model = new UsrOutpostEnhancement();

        $model->usr_user_id = $usrUserId;
        $model->mst_outpost_id = $mstOutpostId;
        $model->mst_outpost_enhancement_id = $mstOutpostEnhancementId;
        $model->level = 1;

        $this->syncModel($model);

        return $model;
    }

    /**
     * @return Collection<UsrOutpostEnhancementInterface>
     */
    public function getList(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }

    public function findByEnhancementId(string $usrUserId, string $enhancementId): ?UsrOutpostEnhancementInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_outpost_enhancement_id',
            $enhancementId,
            function () use ($usrUserId, $enhancementId) {
                return UsrOutpostEnhancement::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_outpost_enhancement_id', $enhancementId)
                    ->first();
            },
        );
    }

    public function getByMstOutpostId(string $usrUserId, string $mstOutpostId): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstOutpostId) {
                return $cache->filter(function (UsrOutpostEnhancementInterface $model) use ($mstOutpostId) {
                    return $model->getMstOutpostId() === $mstOutpostId;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId, $mstOutpostId) {
                return UsrOutpostEnhancement::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_outpost_id', $mstOutpostId)
                    ->get();
            },
        )->keyBy(function (UsrOutpostEnhancementInterface $model) {
            return $model->getMstOutpostEnhancementId();
        });
    }
}
