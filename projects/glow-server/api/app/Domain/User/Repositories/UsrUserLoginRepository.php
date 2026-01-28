<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserLoginInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserLogin::class;

    public function get(string $usrUserId): ?UsrUserLoginInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * ログイン判定前の初期値レコードを作成
     */
    public function create(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
    {
        $model = new UsrUserLogin();

        $model->usr_user_id = $usrUserId;
        $model->first_login_at = null;
        $model->last_login_at = null;
        $model->hourly_accessed_at = $now->toDateTimeString();
        $model->login_count = 0;
        $model->login_day_count = 0;
        $model->login_continue_day_count = 0;
        $model->comeback_day_count = 0;

        $this->syncModel($model);

        return $model;
    }

    public function getOrCreate(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
    {
        $model = $this->get($usrUserId);

        if ($model === null) {
            $model = $this->create($usrUserId, $now);
        }

        return $model;
    }

    public function updateHourlyAccessedAt(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
    {
        $model = $this->get($usrUserId);
        $model->setHourlyAccessedAt($hourlyAccessedAt);
        $this->syncModel($model);

        return $model;
    }

    /**
     * BankF001の判定のために、DB即時保存も実行するメソッド。
     * @param string $usrUserId
     * @param string $hourlyAccessedAt
     * @return UsrUserLoginInterface
     */
    public function updateHourlyAccessedAtWithSave(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
    {
        $model = $this->updateHourlyAccessedAt($usrUserId, $hourlyAccessedAt);
        /** @var Collection<int, UsrUserLogin> $models */
        $models = collect([$model]);
        $this->saveModels($models);

        return $model;
    }
}
