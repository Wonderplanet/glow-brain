<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * 無償一次通貨を登録する際のエンティティ
 *
 * 複数登録時にひとつあたりの情報をまとめるために作成
 */
class FreeCurrencyAddEntity
{
    /**
     * typeに応じてインスタンスを生成
     *
     * @param string $type CurrencyConstants::FREE_CURRENCY_TYPE_*参照
     * @param integer $amount
     * @param Trigger $trigger
     * @return self
     */
    public static function fromType(string $type, int $amount, Trigger $trigger): self
    {
        // typeに応じてamountを設定
        [$ingameAmount, $bonusAmount, $rewardAmount] = CommonUtility::getFreeAmountByType($type, $amount);

        return new self($ingameAmount, $bonusAmount, $rewardAmount, $trigger);
    }

    /**
     * コンストラクタ
     *
     * 同一のユーザーに登録することを前提とするため、ユーザーIDなどの情報は含まない
     *
     * @param integer $ingameAmount 付与する無償一次通貨の数(ingame)
     * @param integer $bonusAmount 付与する無償一次通貨の数(bonus)
     * @param integer $rewardAmount 付与する無償一次通貨の数(reward)
     * @param Trigger $trigger
     */
    public function __construct(
        private int $ingameAmount,
        private int $bonusAmount,
        private int $rewardAmount,
        private Trigger $trigger
    ) {
    }

    /**
     * 付与する無償一次通貨の数(ingame)を取得
     *
     * @return integer
     */
    public function getIngameAmount(): int
    {
        return $this->ingameAmount;
    }

    /**
     * 付与する無償一次通貨の数(bonus)を取得
     *
     * @return integer
     */
    public function getBonusAmount(): int
    {
        return $this->bonusAmount;
    }

    /**
     * 付与する無償一次通貨の数(reward)を取得
     *
     * @return integer
     */
    public function getRewardAmount(): int
    {
        return $this->rewardAmount;
    }

    /**
     * 付与する無償一次通貨の合計数を取得
     *
     * @return integer
     */
    public function getTotalAmount(): int
    {
        return $this->ingameAmount + $this->bonusAmount + $this->rewardAmount;
    }

    /**
     * Triggerを取得
     *
     * @return Trigger
     */
    public function getTrigger(): Trigger
    {
        return $this->trigger;
    }
}
