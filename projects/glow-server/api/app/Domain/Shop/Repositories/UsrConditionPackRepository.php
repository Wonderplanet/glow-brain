<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrConditionPack;
use Illuminate\Support\Collection;

class UsrConditionPackRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrConditionPack::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrConditionPack $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->usr_user_id,
                'mst_pack_id' => $model->mst_pack_id,
                'start_date' => $model->start_date,
            ];
        })->toArray();

        UsrConditionPack::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_pack_id'],
            ['start_date'],
        );
    }

    /**
     * @return Collection<\App\Domain\Shop\Models\UsrConditionPackInterface>
     */
    public function getList(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }

    public function get(string $userId, string $mstPackId): ?UsrConditionPack
    {
        return $this->cachedGetOneWhere(
            $userId,
            'mst_pack_id',
            $mstPackId,
            function () use ($userId, $mstPackId) {
                return UsrConditionPack::query()
                    ->where('usr_user_id', $userId)
                    ->where('mst_pack_id', $mstPackId)
                    ->first();
            }
        );
    }

    /**
     * 配列からモデルインスタンスを生成し、キャッシュ管理に追加する。
     * キャッシュの内容と同期したのちに、キャッシュを考慮した返り値を返す。
     *
     * @param array<int, array<string, string|int>> $values
     * @return Collection
     */
    public function syncModelsByArray(array $values): Collection
    {
        $usrConditionPacks = collect();
        foreach ($values as $value) {
            $usrConditionPack = new UsrConditionPack();

            $usrConditionPack->usr_user_id = $value['usr_user_id'];
            $usrConditionPack->mst_pack_id = $value['mst_pack_id'];
            $usrConditionPack->start_date = $value['start_date'];

            $usrConditionPacks->add($usrConditionPack);
        }

        $this->syncModels($usrConditionPacks);

        return $this->getCacheFilteredByModelKey($usrConditionPacks);
    }
}
