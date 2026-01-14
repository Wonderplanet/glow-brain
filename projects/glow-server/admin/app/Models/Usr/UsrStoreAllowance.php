<?php

namespace App\Models\Usr;

use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance as BaseUsrStoreAllowance;

class UsrStoreAllowance extends BaseUsrStoreAllowance
{

    public const KEY_NAMES = [
        'usr_user_id' => 'ユーザーID',
        'os_platform' => 'OSプラットフォーム',
        'billing_platform' => '課金プラットフォーム',
        'device_id' => 'デバイスID',
        'product_id' => 'プロダクトID',
        'mst_store_product_id' => 'mst_store_product_id',
        'product_sub_id' => 'product_sub_id',
    ];

    public const REQUIRED_COLUMNS = [
        'usr_user_id',
        'product_id',
        'mst_store_product_id',
        'product_sub_id',
        'os_platform',
        'billing_platform'
    ];
}
