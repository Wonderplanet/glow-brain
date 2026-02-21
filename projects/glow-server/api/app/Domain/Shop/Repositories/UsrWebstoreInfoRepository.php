<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Shop\Models\UsrWebstoreInfoInterface;

class UsrWebstoreInfoRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrWebstoreInfo::class;

    public function get(string $usrUserId): ?UsrWebstoreInfoInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    public function create(
        string $usrUserId,
        string $countryCode,
        ?string $osPlatform = null,
        ?string $adId = null
    ): UsrWebstoreInfoInterface {
        $model = new UsrWebstoreInfo();
        $model->usr_user_id = $usrUserId;
        $model->country_code = $countryCode;
        $model->os_platform = $osPlatform;
        $model->ad_id = $adId;

        $this->syncModel($model);

        return $model;
    }
}
