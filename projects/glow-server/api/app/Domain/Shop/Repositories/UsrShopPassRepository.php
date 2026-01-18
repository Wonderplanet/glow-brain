<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Models\UsrShopPassInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrShopPassRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrShopPass::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrShopPassInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_shop_pass_id' => $model->getMstShopPassId(),
                'daily_reward_received_count' => $model->getDailyRewardReceivedCount(),
                'daily_latest_received_at' => $model->getDailyLatestReceivedAt(),
                'start_at' => $model->getStartAt(),
                'end_at' => $model->getEndAt(),
            ];
        })->toArray();

        UsrShopPass::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_shop_pass_id'],
            ['daily_reward_received_count', 'daily_latest_received_at', 'start_at', 'end_at'],
        );
    }

    public function create(
        string $usrUserId,
        string $mstShopPassId,
        CarbonImmutable $now,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
    ): UsrShopPass {
        $usrShopPass = new UsrShopPass();
        $usrShopPass->usr_user_id = $usrUserId;
        $usrShopPass->mst_shop_pass_id = $mstShopPassId;
        $usrShopPass->daily_reward_received_count = 0;
        $usrShopPass->daily_latest_received_at = $now->subDay()->format('Y-m-d H:i:s');
        $usrShopPass->start_at = $startAt->format('Y-m-d H:i:s');
        $usrShopPass->end_at = $endAt->format('Y-m-d H:i:s');

        $this->syncModel($usrShopPass);

        return $usrShopPass;
    }

    public function get(string $usrUserId, string $mstShopPassId): ?UsrShopPass
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_shop_pass_id',
            $mstShopPassId,
            function () use ($usrUserId, $mstShopPassId) {
                return UsrShopPass::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_shop_pass_id', $mstShopPassId)
                    ->first();
            }
        );
    }

    public function getActivePass(string $usrUserId, string $mstShopPassId, CarbonImmutable $now): ?UsrShopPass
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstShopPassId, $now) {
                return $cache->filter(function (UsrShopPassInterface $model) use ($now, $mstShopPassId) {
                    $startAt = CarbonImmutable::parse($model->getStartAt());
                    $endAt = CarbonImmutable::parse($model->getEndAt());
                    return $startAt <= $now && $endAt >= $now && $model->getMstShopPassId() === $mstShopPassId;
                });
            },
            expectedCount: 1,
            dbCallback: function () use ($usrUserId, $mstShopPassId, $now) {
                return UsrShopPass::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_shop_pass_id', $mstShopPassId)
                    ->where('start_at', '<=', $now)
                    ->where('end_at', '>=', $now)
                    ->get();
            }
        )->first();
    }

    /**
     * @return Collection<UsrShopPassInterface>
     */
    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    /**
     * @return Collection<UsrShopPassInterface>
     */
    public function getActiveList(string $usrUserId, CarbonImmutable $now): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($now) {
                return $cache->filter(function (UsrShopPassInterface $model) use ($now) {
                    $startAt = CarbonImmutable::parse($model->getStartAt());
                    $endAt = CarbonImmutable::parse($model->getEndAt());
                    return $startAt <= $now && $endAt >= $now;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId, $now) {
                return UsrShopPass::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('start_at', '<=', $now)
                    ->where('end_at', '>=', $now)
                    ->get();
            }
        );
    }
}
