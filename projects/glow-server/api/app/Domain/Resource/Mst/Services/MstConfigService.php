<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\Item\Constants\ItemConstant;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Mst\Repositories\MstConfigRepository;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\User\Constants\UserConstant;
use Illuminate\Support\Collection;

class MstConfigService
{
    public function __construct(
        private MstConfigRepository $mstConfigRepository,
    ) {
    }

    /**
     * ユニットステータスの指数を取得する
     * @return float
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getUnitStatusExponent(): float
    {
        $key = MstConfigConstant::UNIT_STATUS_EXPONENT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UnitConstant::DEFAULT_UNIT_STATUS_EXPONENT;
        }

        return (float) $value;
    }

    /**
     * 広告視聴スタミナ購入の広告視聴インターバル(分)を取得する
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getDailyBuyStaminaAdIntervalMinutes(): int
    {
        $key = MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES;
        }

        return (int) $value;
    }

    /**
     * 1日の広告視聴スタミナ購入回数の最大値を取得する
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getMaxDailyBuyStaminaAdCount(): int
    {
        $key = MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT;
        }

        return (int) $value;
    }

    /**
     * 1日の広告視聴スタミナ購入で回復する最大スタミナにおけるパーセンテージを取得する
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getBuyStaminaAdPercentageOfMaxStamina(): int
    {
        $key = MstConfigConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA;
        }

        return (int) $value;
    }

    /**
     * ダイヤモンドスタミナ購入で回復する最大スタミナにおけるパーセンテージを取得する
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getBuyStaminaDiamondPercentageOfMaxStamina(): int
    {
        $key = MstConfigConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA;
        }

        return (int) $value;
    }

    /**
     * スタミナ購入に必要なダイヤモンド数を取得する
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getBuyStaminaDiamondAmount(): int
    {
        $key = MstConfigConstant::BUY_STAMINA_DIAMOND_AMOUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::BUY_STAMINA_DIAMOND_AMOUNT;
        }

        return (int) $value;
    }

    public function getUserNameChangeIntervalHours(): int
    {
        $key = MstConfigConstant::USER_NAME_CHANGE_INTERVAL_HOURS;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::USER_NAME_CHANGE_INTERVAL_HOURS;
        }

        return (int) $value;
    }

    public function getStageContinueDiamondAmount(): int
    {
        $key = MstConfigConstant::STAGE_CONTINUE_DIAMOND_AMOUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = StageConstant::CONTINUE_DIAMOND_COST;
        }

        return (int) $value;
    }

    public function getDebugGrantArtworkIds(): Collection
    {
        $key = MstConfigConstant::DEBUG_GRANT_ARTWORK_IDS;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            return collect();
        }

        return collect(explode(',', $value));
    }

    public function getDebugDefaultOutpostArtworkId(): ?string
    {
        $key = MstConfigConstant::DEBUG_DEFAULT_OUTPOST_ARTWORK_ID;
        return $this->mstConfigRepository->getValueByKey($key);
    }

    /**
     * 1スタミナ回復するのにかかる時間(分)を取得
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getRecoveryStaminaMinute(): int
    {
        $key = MstConfigConstant::RECOVERY_STAMINA_MINUTE;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::RECOVERY_STAMINA_MINUTE;
        }

        return (int) $value;
    }


    /**
     * メインクエスト未クリア時の探索報酬として参照するステージIDを取得
     * mst_configsにも設定がない場合は、探索報酬なしとしてnullを返す
     */
    public function getIdleIncentiveInitialRewardMstStageId(): ?string
    {
        $key = MstConfigConstant::IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID;
        return $this->mstConfigRepository->getValueByKey($key);
    }

    /**
     * 強化クエストの報酬となるコイン計算用「N時間分探索コイン × 係数」のNの指定を取得
     * @return int
     */
    public function getEnhanceQuestIdleCoinRewardHours(): int
    {
        $key = MstConfigConstant::ENHANCE_QUEST_IDLE_COIN_REWARD_HOURS;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            // 係数と乗算して報酬量が0にならないために、0以外の整数の最低値の1をデフォルトとしている
            $value = 1;
        }

        return (int) $value;
    }

    /**
     * 強化クエストのステージに対する通常の挑戦回数を取得
     * @return int
     */
    public function getEnhanceQuestChallengeLimit(): int
    {
        $key = MstConfigConstant::ENHANCE_QUEST_CHALLENGE_LIMIT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            // 挑戦できない状態にしないために、最低でも1回挑戦できるようにしている
            $value = 1;
        }

        return (int) $value;
    }

    /**
     * 強化クエストのステージに対する広告視聴による挑戦回数を取得
     * @return int
     */
    public function getEnhanceQuestChallengeAdLimit(): int
    {
        $key = MstConfigConstant::ENHANCE_QUEST_CHALLENGE_AD_LIMIT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            // 広告視聴による挑戦ができない状態にしないために、最低でも1回挑戦できるようにしている
            $value = 1;
        }

        return (int) $value;
    }

    /**
     * 降臨バトルのランキング集計時間(時間)を取得
     * @return int
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getAdventBattleRankingAggregateHours(): int
    {
        $key = MstConfigConstant::ADVENT_BATTLE_RANKING_AGGREGATE_HOURS;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = AdventBattleConstant::DEFAULT_RANKING_AGGREGATE_HOURS;
        }

        return (int) $value;
    }

    /**
     * ユニットのレベルアップキャップの値を取得
     * @return int
     */
    public function getUnitLevelUpCap(): int
    {
        $key = MstConfigConstant::UNIT_LEVEL_CAP;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = 0;
        }

        return (int) $value;
    }

    /**
     * 図鑑新着バッチ消失時の付与無償プリズムの数
     * @return int
     */
    public function getEncyclopediaFirstCollectionReward(): int
    {
        $key = MstConfigConstant::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = 0;
        }

        return (int) $value;
    }

    /**
     * 広告視聴でステージを続行できる最大回数を取得
     * @return int
     */
    public function getAdContinueMaxCount(): int
    {
        $key = MstConfigConstant::AD_CONTINUE_MAX_COUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = 0;
        }

        return (int) $value;
    }

    /**
     * PVP挑戦用のチケットアイテムIDを取得
     * @return string|null
     */
    public function getPvpChallengeItemId(): ?string
    {
        $key = MstConfigConstant::PVP_CHALLENGE_ITEM_ID;
        $value = $this->mstConfigRepository->getValueByKey($key);
        return $value;
    }

    /**
     * PVPランキングの表示件数を取得
     * @return ?int
     */
    public function getPvpRankingDisplayCount(): ?int
    {
        $key = MstConfigConstant::PVP_RANKING_DISPLAY_COUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            return PvpConstant::RANKING_DISPLAY_DEFAULT_COUNT;
        }

        return (int) $value;
    }

    /**
     * ユーザーアイテムの最大所持数を取得
     * @return int
     */
    public function getUserItemMaxAmount(): int
    {
        $key = MstConfigConstant::USER_ITEM_MAX_AMOUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = ItemConstant::MAX_POSESSION_ITEM_COUNT;
        }

        return (int) $value;
    }

    /**
     * ユーザーコイン最大所持数を取得
     * @return int
     */
    public function getUserCoinMaxAmount(): int
    {
        $key = MstConfigConstant::USER_COIN_MAX_AMOUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::MAX_COIN_COUNT;
        }

        return (int) $value;
    }

    /**
     * ユーザースタミナ最大所持数を取得
     * @return int
     */
    public function getUserStaminaMaxAmount(): int
    {
        $key = MstConfigConstant::USER_STAMINA_MAX_AMOUNT;
        $value = $this->mstConfigRepository->getValueByKey($key);

        if (is_null($value)) {
            $value = UserConstant::MAX_STAMINA;
        }

        return (int) $value;
    }
}
