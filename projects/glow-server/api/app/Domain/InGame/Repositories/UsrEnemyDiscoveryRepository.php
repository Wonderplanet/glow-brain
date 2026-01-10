<?php

declare(strict_types=1);

namespace App\Domain\InGame\Repositories;

use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\InGame\Models\UsrEnemyDiscoveryInterface;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrEnemyDiscoveryRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrEnemyDiscovery::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrEnemyDiscovery $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_enemy_character_id' => $model->getMstEnemyCharacterId(),
                'is_new_encyclopedia' => $model->getIsNewEncyclopedia(),
            ];
        })->toArray();

        UsrEnemyDiscovery::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_enemy_character_id'],
        );
    }

    public function create(string $usrUserId, string $mstEnemyCharacterId): UsrEnemyDiscoveryInterface
    {
        $model = new UsrEnemyDiscovery();

        $model->usr_user_id = $usrUserId;
        $model->mst_enemy_character_id = $mstEnemyCharacterId;
        $model->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NEW->value;
        return $model;
    }

    public function getByMstEnemyCharacterIds(string $usrUserId, Collection $mstEnemyCharacterIds): Collection
    {
        if ($mstEnemyCharacterIds->isEmpty()) {
            return collect();
        }
        $mstEnemyCharacterIds = $mstEnemyCharacterIds->unique();

        $modelKeys = $mstEnemyCharacterIds->map(function (string $mstEnemyCharacterId) use ($usrUserId) {
            return UsrEnemyDiscovery::makeModelKeyAsStatic($usrUserId, $mstEnemyCharacterId);
        });

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($modelKeys) {
                return $cache->only($modelKeys->all());
            },
            expectedCount: $mstEnemyCharacterIds->count(),
            dbCallback: function () use ($usrUserId, $mstEnemyCharacterIds) {
                return UsrEnemyDiscovery::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_enemy_character_id', $mstEnemyCharacterIds->all())
                    ->get();
            },
        );
    }

    /**
     * @return Collection<UsrEnemyDiscoveryInterface>
     */
    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }
}
