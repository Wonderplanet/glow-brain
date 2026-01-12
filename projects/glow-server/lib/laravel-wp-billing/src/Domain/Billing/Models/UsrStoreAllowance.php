<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use WonderPlanet\Domain\Currency\Models\BaseUsrModel;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;

/**
 * ユーザーの購入許可レコードを管理するModel
 *
 * @property string $id
 * @property string $usr_user_id
 * @property string $product_id
 * @property string $mst_store_product_id
 * @property string $product_sub_id
 * @property string $os_platform
 * @property string $billing_platform
 * @property string $device_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UsrStoreAllowance extends BaseUsrModel
{
    use HasEntityTrait;

    protected $fillable = [
        'usr_user_id',
        'product_id',
        'mst_store_product_id',
        'product_sub_id',
        'os_platform',
        'billing_platform',
        'device_id',
    ];
}
