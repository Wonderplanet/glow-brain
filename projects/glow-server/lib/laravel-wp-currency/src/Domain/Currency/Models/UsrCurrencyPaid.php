<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use WonderPlanet\Domain\Billing\Entities\UsrStoreProductHistoryEntity;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;

/**
 * ユーザーの有償一次通貨レコード
 *
 * @property string $id
 * @property int $seq_no
 * @property string $usr_user_id
 * @property int $left_amount
 * @property string  $purchase_price
 * @property int $purchase_amount
 * @property string  $price_per_amount
 * @property int $vip_point
 * @property string  $currency_code
 * @property string $receipt_unique_id
 * @property int $is_sandbox 0:本番, 1:サンドボックス
 * @property string $os_platform
 * @property string $billing_platform
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class UsrCurrencyPaid extends BaseUsrModel
{
    use HasEntityTrait;
    use SoftDeletes;

    private ?UsrStoreProductHistoryEntity $usr_store_product_history_entity;

    /**
     * 有償一次通貨と紐づく購入情報のエンティティをセット
     *  UsrStoreProductHistoryが複合主キーのためリレーションのhasOneで紐づけるのが難しくsetterを作成
     *
     * @param UsrStoreProductHistoryEntity|null $usrStoreProductHistoryEntity
     * @return void
     */
    public function setUsrStoreProductHistoryEntity(?UsrStoreProductHistoryEntity $usrStoreProductHistoryEntity): void
    {
        $this->usr_store_product_history_entity = $usrStoreProductHistoryEntity;
    }

    /**
     * 有償一次通貨と紐づく購入情報のエンティティを取得
     *
     * @return UsrStoreProductHistoryEntity|null
     */
    public function getUsrStoreProductHistoryEntity(): ?UsrStoreProductHistoryEntity
    {
        return $this->usr_store_product_history_entity ?? null;
    }
}
