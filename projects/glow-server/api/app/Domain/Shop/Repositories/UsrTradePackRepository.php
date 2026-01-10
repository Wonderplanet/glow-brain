<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrTradePack;
use App\Domain\Shop\Models\UsrTradePackInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrTradePackRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrTradePack::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrTradePackInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_pack_id' => $model->getMstPackId(),
                'daily_trade_count' => $model->getDailyTradeCount(),
                'last_reset_at' => $model->getLastResetAt(),
            ];
        })->toArray();

        UsrTradePack::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_pack_id'],
            ['daily_trade_count', 'last_reset_at'],
        );
    }

    public function create(string $usrUserId, string $mstPackId, CarbonImmutable $now): UsrTradePack
    {
        $usrShopPass = new UsrTradePack();
        $usrShopPass->usr_user_id = $usrUserId;
        $usrShopPass->mst_pack_id = $mstPackId;
        $usrShopPass->daily_trade_count = 0;
        $usrShopPass->last_reset_at = $now->format('Y-m-d H:i:s');

        $this->syncModel($usrShopPass);

        return $usrShopPass;
    }

    public function get(string $usrUserId, string $mstPackId): ?UsrTradePack
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_pack_id',
            $mstPackId,
            function () use ($usrUserId, $mstPackId) {
                return UsrTradePack::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_pack_id', $mstPackId)
                    ->first();
            }
        );
    }

    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function findOrCreate(string $usrUserId, string $mstPackId, CarbonImmutable $now): UsrTradePack
    {
        $usrShopItem = $this->get($usrUserId, $mstPackId);
        if ($usrShopItem === null) {
            $usrShopItem = $this->create($usrUserId, $mstPackId, $now);
        }
        return $usrShopItem;
    }
}
