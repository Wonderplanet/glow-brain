<?php

namespace App\Models\Usr;

use WonderPlanet\Domain\Billing\Models\UsrStoreInfo as BaseUsrStoreInfo;

class UsrStoreInfo extends BaseUsrStoreInfo
{
    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }
}
