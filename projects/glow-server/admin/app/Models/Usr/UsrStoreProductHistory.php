<?php

namespace App\Models\Usr;

use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory as BaseUsrStoreProductHistory;

class UsrStoreProductHistory extends BaseUsrStoreProductHistory
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
