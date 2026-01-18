<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserBuyCountInterface;

class UsrUserBuyCountRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserBuyCount::class;

    public function findByUsrUserId(string $usrUserId): ?UsrUserBuyCountInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    public function create(string $usrUserId): UsrUserBuyCountInterface
    {
        $usrUserBuyCount = new UsrUserBuyCount();
        $usrUserBuyCount->id = $usrUserBuyCount->newUniqueId();
        $usrUserBuyCount->usr_user_id = $usrUserId;
        $usrUserBuyCount->daily_buy_stamina_ad_count = 0;
        $usrUserBuyCount->daily_buy_stamina_ad_at = null;

        $this->syncModel($usrUserBuyCount);

        return $usrUserBuyCount;
    }

    public function findOrCreate(string $usrUserId): UsrUserBuyCountInterface
    {
        $usrUserBuyCount = $this->findByUsrUserId($usrUserId);
        if (is_null($usrUserBuyCount)) {
            $usrUserBuyCount = $this->create($usrUserId);
        }

        return $usrUserBuyCount;
    }
}
