<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 有償一次通貨のログ記録用クラス
 *
 * @property string $id
 * @property int $seq_no
 * @property string $usr_user_id
 * @property string $currency_paid_id
 * @property string $receipt_unique_id
 * @property int $is_sandbox 0:本番, 1:サンドボックス
 * @property string $query
 * @property string $purchase_price
 * @property int $purchase_amount
 * @property string $price_per_amount
 * @property int $vip_point
 * @property string $currency_code
 * @property int $before_amount
 * @property int $change_amount
 * @property int $current_amount
 * @property string $os_platform
 * @property string $billing_platform
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
class LogCurrencyPaid extends BaseLogModel
{
    /**
     * 変更タイプ: 新しく購入してレコードを追加
     */
    public const QUERY_INSERT = 'insert';
    /**
     * 変更タイプ: 既存のレコードを更新
     */
    public const QUERY_UPDATE = 'update';
    /**
     * 変更タイプ: 消費しきったレコードを削除
     */
    public const QUERY_DELETE = 'delete';
}
