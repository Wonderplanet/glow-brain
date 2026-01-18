<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Models;

use WonderPlanet\Domain\Currency\Models\BaseLogModel;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;

/**
 * 購入トランザクションを強制クローズした際に発行するログ
 *
 * @property string $id
 * @property string $usr_user_id ユーザーID
 * @property string $platform_product_id プラットフォーム側で定義しているproduct_id
 * @property string $mst_store_product_id マスターテーブルのプロダクトID
 * @property string $product_sub_id 購入対象のproduct_sub_id
 * @property string $product_sub_name 実際の販売商品名
 * @property string $raw_receipt 復号済み生レシートデータ
 * @property string $raw_price_string クライアントから送られてきた単価付き購入価格
 * @property string $currency_code ISO 4217の通貨コード
 * @property string $receipt_unique_id レシート記載、ユニークなID
 * @property string $receipt_bundle_id レシート記載、ストアから送られてきた商品のバンドルID
 * @property string $os_platform OSプラットフォーム
 * @property string $billing_platform AppStore / GooglePlay
 * @property string $device_id ユーザーの使用しているデバイス識別子
 * @property string $purchase_price ストアから送られてきた実際の購入価格
 * @property integer $is_sandbox サンドボックス・テスト課金から購入したら1, 本番購入なら0
 * @property string $log_store_id 失敗したストア購入ログのレコードID
 * @property string $usr_store_product_history_id 失敗したストア商品購入テーブルのレコードID
 * @property string $trigger_type ロギング契機
 * @property string $trigger_name ロギング契機の日本語名
 * @property string $trigger_id ロギング契機に対応するID
 * @property string $trigger_detail その他の付与情報 (JSON)
 * @property string $request_id_type リクエスト識別IDの種類
 * @property string $request_id リクエスト識別ID
 * @property string $nginx_request_id nginxのリクエスト識別ID
 * @property \Illuminate\Support\Carbon $created_at 作成日時
 * @property \Illuminate\Support\Carbon $updated_at 更新日時
*/
class LogCloseStoreTransaction extends BaseLogModel
{
    use HasEntityTrait;

    protected $fillable = [
        'usr_user_id',
        'platform_product_id',
        'mst_store_product_id',
        'product_sub_id',
        'product_sub_name',
        'raw_receipt',
        'raw_price_string',
        'currency_code',
        'receipt_unique_id',
        'receipt_bundle_id',
        'os_platform',
        'billing_platform',
        'device_id',
        'purchase_price',
        'is_sandbox',
        'log_store_id',
        'usr_store_product_history_id',
        'trigger_type',
        'trigger_name',
        'trigger_id',
        'trigger_detail',
        'request_id_type',
        'request_id',
        'nginx_request_id',
    ];
}
