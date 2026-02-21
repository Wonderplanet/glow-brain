<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use WonderPlanet\Domain\Currency\Models\BaseUsrModel;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;

/**
 * ユーザーのショップ購入履歴を管理するModel
 *
 * @property string $id
 * @property string $receipt_unique_id
 * @property int $order_id
 * @property string $invoice_id
 * @property string $transaction_id
 * @property string $os_platform
 * @property string $billing_platform
 * @property string $usr_user_id
 * @property string $device_id
 * @property int $age
 * @property string $product_sub_id
 * @property string $platform_product_id
 * @property string $mst_store_product_id
 * @property string $currency_code
 * @property string $receipt_bundle_id
 * @property string $receipt_purchase_token
 * @property int $paid_amount
 * @property int $free_amount
 * @property string $purchase_price
 * @property string $price_per_amount
 * @property int $vip_point
 * @property int $is_sandbox 0:本番, 1:サンドボックス
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class UsrStoreProductHistory extends BaseUsrModel
{
    use HasEntityTrait;
    use SoftDeletes;
}
