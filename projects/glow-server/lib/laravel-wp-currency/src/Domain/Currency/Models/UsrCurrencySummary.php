<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use WonderPlanet\Domain\Currency\Models\HasEntityTrait;
use WonderPlanet\Domain\Currency\Models\PlatformPaidTrait;

/**
 * ユーザーの通貨情報レコードを管理するモデル
 *
 * @property string $id
 * @property string $usr_user_id
 * @property int $paid_amount_apple
 * @property int $paid_amount_google
 * @property int $free_amount
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class UsrCurrencySummary extends BaseUsrModel
{
    use PlatformPaidTrait;
    use HasEntityTrait;
    use SoftDeletes;

    public function getPaidAmountApple(): int
    {
        return $this->paid_amount_apple;
    }
    public function getPaidAmountGoogle(): int
    {
        return $this->paid_amount_google;
    }

    /**
     * 有償一次通貨の合計を取得する
     *
     * @return integer
     */
    public function getTotalPaidAmount(): int
    {
        return $this->paid_amount_apple + $this->paid_amount_google;
    }

    /**
     * プラットフォーム合算の有償・無償一次通貨の合計を取得する
     *
     * プラットフォームごとの有償一次通貨と無償一次通貨を合わせた総所持数となる
     *
     * @return integer
     */
    public function getTotalCurrencyAmount(): int
    {
        return $this->getTotalPaidAmount() + $this->free_amount;
    }

    /**
     * 無償一次通貨を取得する
     *
     * @return integer
     */
    public function getFreeAmount(): int
    {
        return $this->free_amount;
    }
}
