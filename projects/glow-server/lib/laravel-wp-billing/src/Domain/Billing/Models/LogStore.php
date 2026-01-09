<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use WonderPlanet\Domain\Currency\Models\BaseLogModel;

/**
 * ショップ購入ログ
 *
 * @property string $id
 * @property int|null $seq_no
 * @property string $usr_user_id
 * @property string $device_id
 * @property int $age
 * @property string $platform_product_id
 * @property string $mst_store_product_id
 * @property string $product_sub_id
 * @property string $product_sub_name
 * @property string $raw_receipt
 * @property string $raw_price_string
 * @property string $currency_code
 * @property string $receipt_unique_id
 * @property string $receipt_bundle_id
 * @property string $os_platform
 * @property string $billing_platform
 * @property int $paid_amount
 * @property int $free_amount
 * @property string $purchase_price
 * @property string $price_per_amount
 * @property int $vip_point
 * @property int $is_sandbox 0:本番, 1:サンドボックス
 * @property string $trigger_type
 * @property string $trigger_id
 * @property string $trigger_name
 * @property string $trigger_detail
 * @property string $request_id_type
 * @property string $request_id
 * @property string $nginx_request_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LogStore extends BaseLogModel
{
}
