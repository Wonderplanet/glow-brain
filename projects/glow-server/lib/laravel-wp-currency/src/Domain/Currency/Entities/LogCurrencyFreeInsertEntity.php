<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * 無償一次通貨ログを複数登録する際のエンティティ
 *
 * 同一のユーザーに対して複数の無償一次通貨を登録する際の情報をまとめるために作成
 */
class LogCurrencyFreeInsertEntity
{
    /**
     * コンストラクタ
     *
     * DBテーブルカラムに合わせたプロパティ
     * 同一のユーザーに登録することを前提とするため、ユーザーIDなどの情報は含まない
     *
     * @param integer $beforeIngameAmount
     * @param integer $beforeBonusAmount
     * @param integer $beforeRewardAmount
     * @param integer $changeIngameAmount
     * @param integer $changeBonusAmount
     * @param integer $changeRewardAmount
     * @param integer $currentIngameAmount
     * @param integer $currentBonusAmount
     * @param integer $currentRewardAmount
     * @param Trigger $trigger
     */
    public function __construct(
        public int $beforeIngameAmount,
        public int $beforeBonusAmount,
        public int $beforeRewardAmount,
        public int $changeIngameAmount,
        public int $changeBonusAmount,
        public int $changeRewardAmount,
        public int $currentIngameAmount,
        public int $currentBonusAmount,
        public int $currentRewardAmount,
        public Trigger $trigger
    ) {
    }
}
