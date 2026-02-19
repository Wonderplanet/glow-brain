<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ユーザーの無償一次通貨レコードを管理するモデル
 *
 * @property string $id
 * @property string $usr_user_id
 * @property int $ingame_amount
 * @property int $bonus_amount
 * @property int $reward_amount
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class UsrCurrencyFree extends BaseUsrModel
{
    use SoftDeletes;
    use HasEntityTrait;

    /**
     * デフォルト値の設定
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'ingame_amount' => 0,
        'bonus_amount' => 0,
        'reward_amount' => 0,
    ];

    /**
     * 無償一次通貨の現在の合計を取得する
     *
     * @return integer
     */
    public function getTotalAmount(): int
    {
        return $this->ingame_amount + $this->bonus_amount + $this->reward_amount;
    }

    /**
     * 無償一次通貨(経路:ゲーム内、配布)を取得する
     *
     * @return integer
     */
    public function getIngameAmount(): int
    {
        return $this->ingame_amount;
    }

    /**
     * 無償一次通貨(経路:ショップ販売の追加付与)を取得する
     *
     * @return integer
     */
    public function getBonusAmount(): int
    {
        return $this->bonus_amount;
    }

    /**
     * 無償一次通貨(経路:広告視聴など)を取得する
     *
     * @return integer
     */
    public function getRewardAmount(): int
    {
        return $this->reward_amount;
    }
}
