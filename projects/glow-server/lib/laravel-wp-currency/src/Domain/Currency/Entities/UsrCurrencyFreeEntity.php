<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Currency\Models\UsrCurrencyFree;

/**
 * usr_currency_freeのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrCurrencyFreeEntity extends BaseModelEntity
{
    // DBテーブルカラムのプロパティ
    public string $id;
    public string $usr_user_id;
    public int $ingame_amount;
    public int $bonus_amount;
    public int $reward_amount;

    public function __construct(UsrCurrencyFree $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->usr_user_id = $model->usr_user_id;
        $this->ingame_amount = $model->ingame_amount;
        $this->bonus_amount = $model->bonus_amount;
        $this->reward_amount = $model->reward_amount;
    }

    // getter
    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getIngameAmount(): int
    {
        return $this->ingame_amount;
    }

    public function getBonusAmount(): int
    {
        return $this->bonus_amount;
    }

    public function getRewardAmount(): int
    {
        return $this->reward_amount;
    }
}
