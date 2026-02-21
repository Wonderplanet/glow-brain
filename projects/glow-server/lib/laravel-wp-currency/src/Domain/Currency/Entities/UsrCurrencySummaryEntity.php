<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Currency\Models\PlatformPaidTrait;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

/**
 * usr_currency_summaryのデータを所持するエンティティ
 *
 * 読み取り専用でデータのみ使用するため作成
 */
class UsrCurrencySummaryEntity extends BaseModelEntity
{
    use PlatformPaidTrait;

    // DBテーブルカラムのプロパティ
    public string $id;
    public string $usr_user_id;
    public int $paid_amount_apple;
    public int $paid_amount_google;
    public int $paid_amount_share;
    public int $free_amount;

    public function __construct(UsrCurrencySummary $model)
    {
        parent::__construct($model);

        $this->id = $model->id;
        $this->usr_user_id = $model->usr_user_id;
        $this->paid_amount_apple = $model->paid_amount_apple;
        $this->paid_amount_google = $model->paid_amount_google;
        $this->paid_amount_share = $model->paid_amount_share;
        $this->free_amount = $model->free_amount;
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

    public function getPaidAmountApple(): int
    {
        return $this->paid_amount_apple + $this->paid_amount_share;
    }

    public function getPaidAmountGoogle(): int
    {
        return $this->paid_amount_google + $this->paid_amount_share;
    }

    public function getPaidAmountShare(): int
    {
        return $this->paid_amount_share;
    }

    public function getTotalPaidAmount(): int
    {
        return $this->paid_amount_apple + $this->paid_amount_google + $this->paid_amount_share;
    }

    public function getFreeAmount(): int
    {
        return $this->free_amount;
    }

    public function getTotalAmount(): int
    {
        return $this->getFreeAmount() + $this->getTotalPaidAmount();
    }
}
