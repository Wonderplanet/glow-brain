<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use WonderPlanet\Domain\Currency\Models\BaseLogModel;

/**
 * 購入許可ログ
 * 購入許可時に前のレコードが残っていた場合など、想定外の挙動の際に参照できるようにするためのログ
 *
 * @property string $id
 * @property string $usr_user_id
 * @property string $product_id
 * @property string $mst_store_product_id
 * @property string $product_sub_id
 * @property string $os_platform
 * @property string $billing_platform
 * @property string $device_id
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
class LogAllowance extends BaseLogModel
{
}
