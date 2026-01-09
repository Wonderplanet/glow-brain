<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Repositories;

use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use Carbon\CarbonImmutable;

class UsrIdleIncentiveRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrIdleIncentive::class;

    public function get(string $usrUserId): ?UsrIdleIncentiveInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    public function create(string $usrUserId, CarbonImmutable $now): UsrIdleIncentiveInterface
    {
        $model = new UsrIdleIncentive();

        $model->usr_user_id = $usrUserId;
        $model->diamond_quick_receive_count = 0;
        $model->ad_quick_receive_count = 0;
        $model->idle_started_at = $now->toDateTimeString();
        $model->diamond_quick_receive_at = $now->toDateTimeString();
        $model->ad_quick_receive_at = $now->toDateTimeString();
        $model->reward_mst_stage_id = null; // 初期状態では報酬ステージは未設定

        $this->syncModel($model);

        return $model;
    }

    public function findOrCreate(string $usrUserId, CarbonImmutable $now): UsrIdleIncentiveInterface
    {
        $model = $this->get($usrUserId);
        if ($model === null) {
            return $this->create($usrUserId, $now);
        }

        return $model;
    }
}
