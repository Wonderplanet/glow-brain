<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\InGame\Models\UsrEnemyDiscoveryInterface;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Item\Models\UsrItemTradeInterface;
use App\Domain\Message\Entities\Message;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface;
use App\Domain\Outpost\Models\UsrOutpostEnhancementInterface;
use App\Domain\Outpost\Models\UsrOutpostInterface;
use App\Domain\Party\Models\UsrPartyInterface;
use App\Domain\Pvp\Entities\PvpResultPoints;
use App\Domain\Resource\Entities\Rewards\AdventBattleAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleDropReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleFirstClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleMaxScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRaidTotalScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRandomClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRankReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\Rewards\ComebackBonusReward;
use App\Domain\Resource\Entities\Rewards\EncyclopediaFirstCollectionReward;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Entities\Rewards\IdleIncentiveReward;
use App\Domain\Resource\Entities\Rewards\ItemReward;
use App\Domain\Resource\Entities\Rewards\ItemTradeReward;
use App\Domain\Resource\Entities\Rewards\MessageReward;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionReward;
use App\Domain\Resource\Entities\Rewards\PvpTotalScoreReward;
use App\Domain\Resource\Entities\Rewards\ShopPassReward;
use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\StageRandomClearReward;
use App\Domain\Resource\Entities\Rewards\StageSpeedAttackClearReward;
use App\Domain\Resource\Entities\Rewards\StepUpGachaStepReward;
use App\Domain\Resource\Entities\Rewards\UnitEncyclopediaReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;
use App\Domain\Resource\Usr\Entities\UsrConditionPackEntity;
use App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Shop\Entities\CurrencyPurchase;
use App\Domain\Shop\Entities\UsrStoreInfoEntity;
use App\Domain\Shop\Models\UsrConditionPackInterface;
use App\Domain\Shop\Models\UsrShopItemInterface;
use App\Domain\Shop\Models\UsrShopPassInterface;
use App\Domain\Shop\Models\UsrStoreProductInterface;
use App\Domain\Shop\Models\UsrTradePackInterface;
use App\Domain\Stage\Models\UsrStageEventInterface;
use App\Domain\Stage\Models\UsrStageInterface;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\User\Models\UsrUserBuyCountInterface;
use App\Domain\User\Models\UsrUserInterface;
use App\Domain\User\Models\UsrUserLoginInterface;
use App\Domain\User\Models\UsrUserProfileInterface;
use App\Http\Responses\Data\AdventBattleRaidTotalScoreData;
use App\Http\Responses\Data\AdventBattleResultData;
use App\Http\Responses\Data\GameBadgeData;
use App\Http\Responses\Data\MissionStatusData;
use App\Http\Responses\Data\MngInGameNoticeData;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use App\Http\Responses\Data\OprCampaignData;
use App\Http\Responses\Data\PvpHeldStatusData;
use App\Http\Responses\Data\PvpPreviousSeasonResultData;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrInGameStatusData;
use App\Http\Responses\Data\UsrMissionBonusPointData;
use App\Http\Responses\Data\UsrMissionStatusData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrPvpStatusData;
use App\Http\Responses\Data\UsrStageEnhanceStatusData;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\Data\UsrTutorialData;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity as BillingUsrStoreInfoEntity;

/**
 * DomainごとのResponseFactoryで使う、レスポンスデータの生成をまとめたクラス
 *
 * APIレスポンスの内容は、glow-schemaリポジトリのyamlで定義している。
 * 対応するキーとレスポンス内容を全APIで統一するために、ResponseDataFactoryでレスポンスする配列を生成する。
 * 例：usrUserParameterのレスポンスは、usrParameterというキーで返すことになっている。
 *
 * DomainごとのResponseFactoryでやることは、ResultDataの情報を使って、どんなレスポンス配列を作成する必要があるかを把握し、
 * ResponseDataFactoryの関数を組み合わせ、最終的に必要な内容へ調整すること。
 */
class ResponseDataFactory
{
    /**
     * Game
     */

    /**
     * @param array<mixed> $result
     * @param GameBadgeData $gameBadgeData
     * @return array<mixed>
     */
    public function addGameBadgeData(array $result, GameBadgeData $gameBadgeData): array
    {
        $result['badges'] = [
            'unreceivedMissionRewardCount' => $gameBadgeData->unreceivedMissionRewardCount,
            'unreceivedMissionBeginnerRewardCount' => $gameBadgeData->unreceivedMissionBeginnerRewardCount,
            'unopenedMessageCount' => $gameBadgeData->unopenedMessageCount,
            'unreceivedMissionEventRewardCounts' => $this->makeMissionEventRewardCountData(
                $gameBadgeData->unreceivedMissionEventRewardCounts
            ),
            'unreceivedMissionAdventBattleRewardCount' => $gameBadgeData->unreceivedMissionAdventBattleRewardCount,
            'unreceivedMissionArtworkPanelRewardCounts' => $this->makeMissionArtworkPanelRewardCountData(
                $gameBadgeData->unreceivedMissionArtworkPanelRewardCounts
            ),
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MngInGameNoticeData> $mngInGameNoticeDataList
     * @return array<mixed>
     */
    public function addMngInGameNoticeData(array $result, Collection $mngInGameNoticeDataList): array
    {
        $response = [];
        foreach ($mngInGameNoticeDataList as $mngInGameNoticeData) {
            /** @var MngInGameNoticeData $mngInGameNoticeData */
            $response[] = $mngInGameNoticeData->formatToResponse();
        }
        // クライアント側の変更対応を避けるために一旦oprのままにする
        $result['oprInGameNotices'] = $response;

        return $result;
    }

    /**
     * User
     */

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addMyIdData(array $result, UsrUserProfileInterface $usrUserProfile): array
    {
        $result['myId'] = $usrUserProfile->getMyId();
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrParameterData(array $result, UsrParameterData $usrUserParameter): array
    {
        $result['usrParameter'] = [
            'level' => $usrUserParameter->getLevel(),
            'exp' => $usrUserParameter->getExp(),
            'coin' => $usrUserParameter->getCoin(),
            'stamina' => $usrUserParameter->getStamina(),
            'staminaUpdatedAt' => StringUtil::convertToISO8601($usrUserParameter->getStaminaUpdatedAt()),
            'freeDiamond' => $usrUserParameter->getFreeDiamond(),
            'paidDiamondIos' => $usrUserParameter->getPaidDiamondIos(),
            'paidDiamondAndroid' => $usrUserParameter->getPaidDiamondAndroid(),
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrLoginData(array $result, ?UsrUserLoginInterface $usrUserLogin): array
    {
        $response = [];

        if (is_null($usrUserLogin)) {
            $response = [
                'lastLoginAt' => null,
                'loginDayCount' => 0,
                'loginContinueDayCount' => 0,
            ];
        } else {
            $response = [
                'lastLoginAt' => StringUtil::convertToISO8601($usrUserLogin->getLastLoginAt()),
                'loginDayCount' => $usrUserLogin->getLoginDayCount(),
                'loginContinueDayCount' => $usrUserLogin->getLoginContinueDayCount(),
            ];
        }

        $result['usrLogin'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UsrUserProfileInterface $usrUserProfile
     * @return array<mixed>
     */
    public function addUsrProfileData(array $result, UsrUserProfileInterface $usrUserProfile): array
    {
        $result['usrProfile'] = [
            'name' => $usrUserProfile->getName(),
            'nameUpdateAt' => StringUtil::convertToISO8601($usrUserProfile->getNameUpdateAt()),
            'mstUnitId' => $usrUserProfile->getMstUnitId(),
            'mstEmblemId' => $usrUserProfile->getMstEmblemId(),
            'myId' => $usrUserProfile->getMyId(),
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UserLevelUpData $userLevelUpData
     * @return array<mixed>
     */
    public function addUserLevelData(array $result, UserLevelUpData $userLevelUpData): array
    {
        $usrLevelReward = [];
        $emblemDuplicate = false;
        foreach ($userLevelUpData->levelUpRewards as $userLevelUpReward) {
            /** @var UserLevelUpReward $userLevelUpReward */
            $usrLevelReward[] = $userLevelUpReward->formatToResponse();
            if ($userLevelUpReward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_EMBLEM) {
                $emblemDuplicate = true;
            }
        }

        $result['userLevel'] = [
            'beforeExp' => $userLevelUpData->beforeExp,
            'afterExp' => $userLevelUpData->afterExp,
            'usrLevelReward' => $usrLevelReward,
            'isEmblemDuplicated' => $emblemDuplicate,
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param string $tutorialStatus
     * @return array<mixed>
     */
    public function addTutorialStatusData(array $result, string $tutorialStatus): array
    {
        $result['tutorialStatus'] = $tutorialStatus;
        return $result;
    }

    /**
     * Tutorial
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrTutorialData> $usrTutorialDataList
     * @return array<mixed>
     */
    public function addUsrTutorialFreePartData(array $result, Collection $usrTutorialDataList): array
    {
        $response = [];

        foreach ($usrTutorialDataList as $usrTutorialData) {
            /** @var UsrTutorialData $usrTutorialData */
            $response[] = [
                'mstTutorialFunctionName' => $usrTutorialData->mstTutorialFunctionName,
            ];
        }

        $result['usrTutorialFreeParts'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param string|null  $bnidLinkedAt
     * @return array<mixed>
     */
    public function addBnidLinkedAtData(array $result, ?string $bnidLinkedAt): array
    {
        $result['bnidLinkedAt'] = $bnidLinkedAt;
        return $result;
    }

    /**
     * @param array<mixed>     $result
     * @param UsrUserInterface $usrUser
     * @return array<mixed>
     */
    public function addGameStartAtData(array $result, UsrUserInterface $usrUser): array
    {
        $result['gameStartAt'] = $usrUser->getGameStartAt();
        return $result;
    }

    /**
     * @param array<mixed>  $result
     * @return array<mixed>
     */
    public function addSysPvpSeasonData(array $result, SysPvpSeasonEntity $sysPvpSeasonEntity): array
    {
        $result['sysPvpSeason'] = $sysPvpSeasonEntity->formatToResponse();
        return $result;
    }

    /**
     * Gacha
     */

    //     /**
    //     * @param array<mixed> $result
    //     * @param array<\App\Domain\Resource\Mst\Entities\OprGachaNormalPrizeEntity> $oprGachaNormalPrizes
    //     * @param boolean $isMulti
    //     * @return array<string, mixed>
    //     */
    //    public function addOprGachaNormalPrizeData(array $result, array $oprGachaNormalPrizes, bool $isMulti): array
    //    {
    //        $response = [];
    //        /** @var \App\Domain\Resource\Mst\Entities\OprGachaNormalPrizeEntity $oprGachaNormalPrize */
    //        foreach ($oprGachaNormalPrizes as $oprGachaNormalPrize) {
    //            $response[] = [
    //                'mstUnitId' => $oprGachaNormalPrize->getMstUnitId(),
    //            ];
    //        }
    //
    //        if ($isMulti) {
    //            $result['oprGachaNormalPrizes'] = $response;
    //        } else {
    //            $result['oprGachaNormalPrize'] = count($response) > 0 ? $response[0] : [];
    //        }
    //
    //        return $result;
    //    }
    //
    //    /**
    //     * @param array<mixed> $result
    //     * @param UsrGachaNormalInterface $usrGachaNormal
    //     * @return array<mixed>
    //     */
    //    public function addUsrGachaNormalData(array $result, UsrGachaNormalInterface $usrGachaNormal): array
    //    {
    //        $result['usrGachaNormal'] = [
    //            'oprGachaNormalId' => $usrGachaNormal->getOprGachaNormalId(),
    //            'diamondDrawCount' => $usrGachaNormal->getDiamondDrawCount(),
    //            'ticketDrawCount' => $usrGachaNormal->getTicketDrawCount(),
    //            'adDrawCount' => $usrGachaNormal->getAdDrawCount(),
    //            'adDrawResetAt' => (string)$usrGachaNormal->getAdDrawResetAt(),
    //        ];
    //
    //        return $result;
    //    }
    //
    //    /**
    //     * @param array<mixed> $result
    //     * @param Collection<\App\Domain\Resource\Mst\Entities\OprGachaSuperPrizeEntity> $oprGachaSuperPrizes
    //     * @param bool $isMulti
    //     * @return array<string, mixed>
    //     */
    //    public function addOprGachaSuperPrizeData(array $result, Collection $oprGachaSuperPrizes, bool $isMulti): array
    //    {
    //        $response = [];
    //        foreach ($oprGachaSuperPrizes as $oprGachaSuperPrize) {
    //            /** @var \App\Domain\Resource\Mst\Entities\OprGachaSuperPrizeEntity $oprGachaSuperPrize */
    //            $response[] = [
    //                'mstUnitId' => $oprGachaSuperPrize->getMstUnitId(),
    //            ];
    //        }
    //
    //        if ($isMulti) {
    //            $result['oprGachaSuperPrizes'] = $response;
    //        } else {
    //            $result['oprGachaSuperPrize'] = count($response) > 0 ? $response[0] : [];
    //        }
    //
    //        return $result;
    //    }
    //
    //    /**
    //     * @param array<mixed> $result
    //     * @return array<mixed>
    //     */
    //    public function addUsrGachaSuperData(array $result, UsrGachaSuperInterface $usrGachaSuper): array
    //    {
    //        $result['usrGachaSuper'] = [
    //            'oprGachaSuperId' => $usrGachaSuper->getOprGachaSuperId(),
    //            'drawCount' => $usrGachaSuper->getDrawCount(),
    //        ];
    //
    //        return $result;
    //    }
    //
    //    /**
    //     * @param array<mixed> $result
    //     * @param Collection $prizeProbabilityMap
    //     * @return array<mixed>
    //     */
    //    public function addPrizeProbabilityData(array $result, Collection $prizeProbabilityMap)
    //    {
    //        foreach ($prizeProbabilityMap as $mstUnitId => $probability) {
    //            $result['prizeProbabilities'][] = [
    //                'mstUnitId' => $mstUnitId,
    //                'probability' => $probability,
    //            ];
    //        }
    //
    //        return $result;
    //    }

    /**
     * IdleIncentive
     */

    /**
     * @param array<mixed> $result
     * @param Collection<IdleIncentiveReward> $rewards
     * @return array<mixed>
     */
    public function addReceiveRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var IdleIncentiveReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['rewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrIdleIncentiveData(array $result, ?UsrIdleIncentiveInterface $usrIdleIncentive): array
    {
        $key = 'usrIdleIncentive';

        if ($usrIdleIncentive === null) {
            $result[$key] = null;
            return $result;
        }

        $result[$key] = [
            'diamondQuickReceiveCount' => $usrIdleIncentive->getDiamondQuickReceiveCount(),
            'adQuickReceiveCount' => $usrIdleIncentive->getAdQuickReceiveCount(),
            'idleStartedAt' => StringUtil::convertToISO8601($usrIdleIncentive->getIdleStartedAt()),
            'diamondQuickReceiveAt' => StringUtil::convertToISO8601($usrIdleIncentive->getDiamondQuickReceiveAt()),
            'adQuickReceiveAt' => StringUtil::convertToISO8601($usrIdleIncentive->getAdQuickReceiveAt()),
        ];
        return $result;
    }

    /**
     * Item
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrItemInterface> $usrItems
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrItemData(array $result, Collection $usrItems, bool $isMulti): array
    {
        $response = [];
        /** @var UsrItemInterface $usrItem */
        foreach ($usrItems as $usrItem) {
            $response[] = [
                'mstItemId' => $usrItem->getMstItemId(),
                'amount' => $usrItem->getAmount(),
            ];
        }

        if ($isMulti) {
            $result['usrItems'] = $response;
        } else {
            $result['usrItem'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrItemTradeInterface> $usrItemTrades
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrItemTradeData(array $result, Collection $usrItemTrades, bool $isMulti): array
    {
        $response = [];

        if (
            !$isMulti
            && $usrItemTrades->count() === 1
            && $usrItemTrades->first() === null
        ) {
            $result['usrItemTrade'] = null;
            return $result;
        }

        /** @var UsrItemTradeInterface|null $usrItemTrade */
        foreach ($usrItemTrades as $usrItemTrade) {
            $response[] = [
                'mstItemId' => $usrItemTrade->getMstItemId(),
                // クライアントにはリセット考慮した値を渡す
                'tradeAmount' => $usrItemTrade->getResetTradeAmount(),
                'tradeAmountResetAt' => StringUtil::convertToISO8601($usrItemTrade->getTradeAmountResetAt()),
            ];
        }

        if ($isMulti) {
            $result['usrItemTrades'] = $response;
        } else {
            $result['usrItemTrade'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * Shop
     */

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrStoreInfoData(
        array $result,
        null|UsrStoreInfoEntity|BillingUsrStoreInfoEntity $usrStoreInfo,
    ): array {
        $response = [];

        if ($usrStoreInfo === null) {
            $response = [
                'age' => null,
                'currentMonthTotalBilling' => 0,
                'renotifyAt' => null,
            ];
        } else {
            $renotifyAt = $usrStoreInfo->getRenotifyAt();
            if ($renotifyAt !== null) {
                $renotifyAt = StringUtil::convertToISO8601($renotifyAt);
            }

            $response = [
                'age' => $usrStoreInfo->getAge(),
                'currentMonthTotalBilling' => $usrStoreInfo->getPaidPrice(),
                'renotifyAt' => $renotifyAt,
            ];
        }

        $result['usrStoreInfo'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrShopPassInterface> $usrShopPasses
     * @return array<mixed>
     */
    public function addUsrShopPassData(array $result, Collection $usrShopPasses, bool $isMulti = true): array
    {
        $response = [];

        foreach ($usrShopPasses as $usrShopPass) {
            /** @var UsrShopPassInterface $usrShopPass */
            $response[] = [
                'mstShopPassId' => $usrShopPass->getMstShopPassId(),
                'dailyRewardReceivedCount' => $usrShopPass->getDailyRewardReceivedCount(),
                'dailyLatestReceivedAt' => StringUtil::convertToISO8601($usrShopPass->getDailyLatestReceivedAt()),
                'startAt' => StringUtil::convertToISO8601($usrShopPass->getStartAt()),
                'endAt' => StringUtil::convertToISO8601($usrShopPass->getEndAt()),
            ];
        }

        if ($isMulti) {
            $result['usrShopPasses'] = $response;
        } else {
            $result['usrShopPass'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<ShopPassReward> $rewards
     * @return array<mixed>
     */
    public function addShopPassRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var ShopPassReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['shopPassRewards'] = $response;

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @param Collection<CurrencyPurchase> $currencyPurchases
     * @return array<string, mixed>
     */
    public function addShopCurrencyPurchaseData(array $result, Collection $currencyPurchases): array
    {
        $response = [];

        foreach ($currencyPurchases as $currencyPurchase) {
            /** @var CurrencyPurchase $currencyPurchase */
            $response[] = $currencyPurchase->formatToResponse();
        }

        $result['currencyPurchases'] = $response;

        return $result;
    }

    /**
     * gacha
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrGachaInterface> $usrGachas
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrGachaData(array $result, Collection $usrGachas, bool $isMulti): array
    {
        $response = [];
        /** @var UsrGachaInterface $usrGacha */
        foreach ($usrGachas as $usrGacha) {
            $expiresAt = $usrGacha->getExpiresAt();
            $response[] = [
                'oprGachaId' => $usrGacha->getOprGachaId(),
                'adPlayedAt' => StringUtil::convertToISO8601($usrGacha->getAdPlayedAt()),
                'playedAt' => StringUtil::convertToISO8601($usrGacha->getPlayedAt()),
                'adCount' => $usrGacha->getAdCount(),
                'adDailyCount' => $usrGacha->getAdDailyCount(),
                'count' => $usrGacha->getCount(),
                'dailyCount' => $usrGacha->getDailyCount(),
                'currentStepNumber' => $usrGacha->getCurrentStepNumber(),
                'loopCount' => $usrGacha->getLoopCount(),
                'expiresAt' => StringUtil::convertToISO8601($expiresAt),
            ];
        }
        if ($isMulti) {
            $result['usrGachas'] = $response;
        } else {
            $result['usrGacha'] = count($response) > 0 ? $response[0] : [];
        }
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrGachaUpperInterface> $usrGachaUppers
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrGachaUpperData(array $result, Collection $usrGachaUppers, bool $isMulti): array
    {
        $response = [];
        /** @var UsrGachaUpper $usrGachaUpper */
        foreach ($usrGachaUppers as $usrGachaUpper) {
            $response[] = [
                'upperGroup' => $usrGachaUpper->getUpperGroup(),
                'upperType' => $usrGachaUpper->getUpperType(),
                'count' => $usrGachaUpper->getCount(),
            ];
        }
        if ($isMulti) {
            $result['usrGachaUppers'] = $response;
        } else {
            $result['usrGachaUpper'] = count($response) > 0 ? $response[0] : [];
        }
        return $result;
    }

    /**
     * usrStoreProductsレスポンスを配列に追加
     * @param array<mixed> $result
     * @param Collection $oprStoreProducts
     * @param Collection $usrStoreProducts
     * @return array<string, array<string, int|string>>
     */
    public function addUsrStoreProductsData(array $result, Collection $oprStoreProducts, Collection $usrStoreProducts): array
    {
        $result['usrStoreProducts'] = [];
        $usrStoreProductMap = array_column($usrStoreProducts->all(), null, 'product_sub_id');
        foreach ($oprStoreProducts as $oprStoreProduct) {
            /** @var UsrStoreProductInterface $oprStoreProduct */
            $usrStoreProduct = $usrStoreProductMap[$oprStoreProduct->getId()] ?? null;
            if (!is_null($usrStoreProduct)) {
                $result['usrStoreProducts'][] = [
                    'productSubId' => $oprStoreProduct->getId(),
                    'purchaseCount' => $usrStoreProduct->getPurchaseCount(),
                    'purchaseTotalCount' => $usrStoreProduct->getPurchaseTotalCount(),
                ];
            }
        }
        return $result;
    }

    /**
     * usrShopItemsレスポンスを配列に追加
     * @param array<mixed> $result
     * @param Collection<UsrShopItemInterface> $usrShopItems
     * @return array<string, array<string, int|string>>
     */
    public function addUsrShopItemsData(array $result, Collection $usrShopItems): array
    {
        $result['usrShopItems'] = [];
        foreach ($usrShopItems as $usrShopItem) {
            /** @var UsrShopItemInterface $usrShopItem */
            $result['usrShopItems'][] = [
                'mstShopItemId' => $usrShopItem->getMstShopItemId(),
                'tradeCount' => $usrShopItem->getTradeCount(),
                'tradeTotalCount' => $usrShopItem->getTradeTotalCount(),
            ];
        }
        return $result;
    }

    /**
     * usrConditionPacksレスポンスを配列に追加
     * @param array<mixed> $result
     * @param Collection<UsrConditionPackEntity|UsrConditionPackInterface> $usrConditionPacks
     * @return array<string, array<string, int|string>>
     */
    public function addUsrConditionPackData(array $result, Collection $usrConditionPacks): array
    {
        $result['usrConditionPacks'] = [];
        foreach ($usrConditionPacks as $usrConditionPack) {
            /** @var UsrConditionPackEntity|UsrConditionPackInterface $usrConditionPack */
            $result['usrConditionPacks'][] = [
                'mstPackId' => $usrConditionPack->getMstPackId(),
                'startDate' => $usrConditionPack->getStartDate(),
            ];
        }
        return $result;
    }

    /**
     * usrStoreProductレスポンスを配列に追加
     * @param array<mixed> $result
     * @param UsrStoreProductInterface $usrStoreProduct
     * @return array<string, array<string, int|string>>
     */
    public function addUsrStoreProductData(array $result, UsrStoreProductInterface $usrStoreProduct): array
    {
        $result['usrStoreProduct'] = [
            'productSubId' => $usrStoreProduct->getProductSubId(),
            'purchaseCount' => $usrStoreProduct->getPurchaseCount(),
            'purchaseTotalCount' => $usrStoreProduct->getPurchaseTotalCount(),
        ];
        return $result;
    }

    /**
     * Stage
     */

    /**
     * @param array<mixed> $result
     * @param Collection<StageFirstClearReward> $stageFirstClearRewards
     * @param Collection<StageAlwaysClearReward> $stageAlwaysClearRewards
     * @param Collection<StageRandomClearReward> $stageRandomClearRewards
     * @param Collection<StageSpeedAttackClearReward> $stageSpeedAttackClearRewards
     * @return array<mixed>
     */
    public function addStageRewardData(
        array $result,
        Collection $stageFirstClearRewards,
        Collection $stageAlwaysClearRewards,
        Collection $stageRandomClearRewards,
        Collection $stageSpeedAttackClearRewards,
    ): array {
        // firstClearは獲得した内容そのままで、alwayClearはtypeとresourceIdごとに個数をまとめた情報をレスポンスする。
        $firstClearRewards = $stageFirstClearRewards->map(function (StageFirstClearReward $reward) {
            return $reward->formatToResponse();
        });
        $alwaysClearRewards = $stageAlwaysClearRewards->groupBy(function (StageAlwaysClearReward $reward) {
            return $reward->getType() . ':' . $reward->getResourceId() . ':' . $reward->getLapNumber();
        })->map(function ($rewards) {
            /** @var StageAlwaysClearReward $targetReward */
            $targetReward = $rewards->first();
            $response = $targetReward->formatToResponse();
            $resourceAmount = $rewards->sum(function (StageAlwaysClearReward $reward) {
                return $reward->getAmount();
            });
            $response['reward']['resourceAmount'] = $resourceAmount;
            return $response;
        });
        $randomClearRewards = $stageRandomClearRewards->map(function (StageRandomClearReward $reward) {
            return $reward->formatToResponse();
        });
        $stageSpeedAttackClearRewards = $stageSpeedAttackClearRewards->map(function (StageSpeedAttackClearReward $reward) {
            return $reward->formatToResponse();
        });
        $result['stageRewards'] = $firstClearRewards
            ->merge($alwaysClearRewards)
            ->merge($randomClearRewards)
            ->merge($stageSpeedAttackClearRewards)
            ->values()
            ->toArray();

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrStageInterface> $usrStages
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrStageData(array $result, Collection $usrStages, bool $isMulti): array
    {
        $response = [];

        /** @var UsrStageInterface $usrStage */
        foreach ($usrStages as $usrStage) {
            $response[] = [
                'mstStageId' => $usrStage->getMstStageId(),
                'clearCount' => $usrStage->getClearCount(),
                'clearTimeMs' => $usrStage->getClearTimeMs(),
            ];
        }

        if ($isMulti) {
            $result['usrStages'] = $response;
        } else {
            $result['usrStage'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrStageEventInterface> $usrStageEvents
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrStageEventData(array $result, Collection $usrStageEvents, bool $isMulti): array
    {
        $response = [];

        /** @var UsrStageEventInterface $usrStageEvent */
        foreach ($usrStageEvents as $usrStageEvent) {
            $response[] = [
                'mstStageId' => $usrStageEvent->getMstStageId(),
                'resetClearCount' => $usrStageEvent->getResetClearCount(),
                'resetAdChallengeCount' => $usrStageEvent->getResetAdChallengeCount(),
                'latestResetAt' => StringUtil::convertToISO8601($usrStageEvent->getLatestResetAt()),
                'totalClearCount' => $usrStageEvent->getClearCount(),
                'lastChallengedAt' => StringUtil::convertToISO8601($usrStageEvent->getLastChallengedAt()),
                'clearTimeMs' => $usrStageEvent->getClearTimeMs(),
                'resetClearTimeMs' => $usrStageEvent->getResetClearTimeMs(),
            ];
        }

        if ($isMulti) {
            $result['usrStageEvents'] = $response;
        } else {
            $result['usrStageEvent'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrStageEnhanceStatusData> $usrStageEnhanceStatusDataList
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrStageEnhanceData(
        array $result,
        Collection $usrStageEnhanceStatusDataList,
        bool $isMulti,
    ): array {
        $response = [];

        /** @var UsrStageEnhanceStatusData $usrStageEnhanceStatusData */
        foreach ($usrStageEnhanceStatusDataList as $usrStageEnhanceStatusData) {
            $response[] = [
                'mstStageId' => $usrStageEnhanceStatusData->getMstStageId(),
                'resetChallengeCount' => $usrStageEnhanceStatusData->getResetChallengeCount(),
                'resetAdChallengeCount' => $usrStageEnhanceStatusData->getResetAdChallengeCount(),
                'maxScore' => $usrStageEnhanceStatusData->getMaxScore(),
            ];
        }

        if ($isMulti) {
            $result['usrStageEnhances'] = $response;
        } else {
            $result['usrStageEnhance'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * AdventBattle
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrAdventBattleInterface> $usrAdventBattles
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrAdventBattleData(array $result, Collection $usrAdventBattles, bool $isMulti): array
    {
        $response = [];
        foreach ($usrAdventBattles as $usrAdventBattle) {
            $response[] = [
                'mstAdventBattleId' => $usrAdventBattle->getMstAdventBattleId(),
                'maxScore' => $usrAdventBattle->getMaxScore(),
                'totalScore' => $usrAdventBattle->getTotalScore(),
                'resetChallengeCount' => $usrAdventBattle->getResetChallengeCount(),
                'resetAdChallengeCount' => $usrAdventBattle->getResetAdChallengeCount(),
                'clearCount' => $usrAdventBattle->getClearCount(),
                'maxReceivedMaxScoreReward' => $usrAdventBattle->getMaxReceivedMaxScoreReward(),
            ];
        }

        if ($isMulti) {
            $result['usrAdventBattles'] = $response;
        } else {
            $result['usrAdventBattle'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleReward> $rewards
     * @return array<mixed>
     */
    public function addAdventBattleRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var AdventBattleReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleClearRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleDropReward> $rewards
     * @return array<mixed>
     */
    public function addAdventBattleDropRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var AdventBattleDropReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleDropRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleFirstClearReward> $firstClearRewards
     * @param Collection<AdventBattleAlwaysClearReward> $alwaysClearRewards
     * @param Collection<AdventBattleRandomClearReward> $randomClearRewards
     * @return array<mixed>
     */
    public function addAdventBattleClearRewardData(
        array $result,
        Collection $firstClearRewards,
        Collection $alwaysClearRewards,
        Collection $randomClearRewards,
    ): array {
        $response = [];

        foreach ($firstClearRewards as $reward) {
            /** @var AdventBattleFirstClearReward $reward */
            $response[] = $reward->formatToResponse();
        }
        foreach ($alwaysClearRewards as $reward) {
            /** @var AdventBattleAlwaysClearReward $reward */
            $response[] = $reward->formatToResponse();
        }
        foreach ($randomClearRewards as $reward) {
            /** @var AdventBattleRandomClearReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleClearRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleRankReward> $rewards
     * @return array<mixed>
     */
    public function addAdventBattleRankRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var AdventBattleRankReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleRankRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleRaidTotalScoreReward> $rewards
     * @return array<mixed>
     */
    public function addAdventBattleRaidRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var AdventBattleRaidTotalScoreReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleRaidTotalScoreRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<AdventBattleMaxScoreReward> $rewards
     * @return array<mixed>
     */
    public function addAdventBattleMaxScoreRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var AdventBattleMaxScoreReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['adventBattleMaxScoreRewards'] = $response;

        return $result;
    }


    /**
     * @param array<mixed> $result
     * @param Collection<UsrTradePackInterface> $usrTradePacks
     * @return array<mixed>
     */
    public function addUsrTradePackData(array $result, Collection $usrTradePacks): array
    {
        $response = [];

        foreach ($usrTradePacks as $usrTradePack) {
            /** @var UsrTradePackInterface $usrTradePack */
            $response[] = [
                'mstPackId' => $usrTradePack->getMstPackId(),
                'dailyTradeCount' => $usrTradePack->getDailyTradeCount(),
            ];
        }

        $result['usrTradePacks'] = $response;
        return $result;
    }

    /**
     * @param array<mixed>                $result
     * @param AdventBattleResultData|null $adventBattleResultData
     * @return array<mixed>
     */
    public function addAdventBattleResultData(array $result, ?AdventBattleResultData $adventBattleResultData): array
    {
        $result['adventBattleResult'] = $adventBattleResultData?->formatToResponse() ?? null;
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param AdventBattleRaidTotalScoreData|null $adventBattleRaidTotalScoreData
     * @return array<mixed>
     */
    public function addAdventBattleRaidTotalScoreData(
        array $result,
        ?AdventBattleRaidTotalScoreData $adventBattleRaidTotalScoreData
    ): array {
        $result['adventBattleRaidTotalScore'] = $adventBattleRaidTotalScoreData?->formatToResponse() ?? null;
        return $result;
    }

    /**
     * InGame
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrEnemyDiscoveryEntity|UsrEnemyDiscoveryInterface> $usrEnemyDiscoveries
     * @return array<mixed>
     */
    public function addUsrEnemyDiscoveryData(array $result, Collection $usrEnemyDiscoveries): array
    {
        $response = [];
        foreach ($usrEnemyDiscoveries as $usrEnemyDiscovery) {
            /** @var UsrEnemyDiscoveryEntity|UsrEnemyDiscoveryInterface $usrEnemyDiscovery */
            $response[] = [
                'mstEnemyCharacterId' => $usrEnemyDiscovery->getMstEnemyCharacterId(),
                'isNewEncyclopedia' => $usrEnemyDiscovery->getIsNewEncyclopedia(),
            ];
        }
        $result['usrEnemyDiscoveries'] = $response;
        return $result;
    }

    /**
     * Unit
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrUnitInterface|UsrUnitEntity> $usrUnits
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrUnitData(array $result, Collection $usrUnits, bool $isMulti): array
    {
        $response = [];
        /** @var UsrUnitInterface|UsrUnitEntity $usrUnit */
        foreach ($usrUnits as $usrUnit) {
            $response[] = [
                'usrUnitId' => $usrUnit->getId(),
                'mstUnitId' => $usrUnit->getMstUnitId(),
                'level' => $usrUnit->getLevel(),
                'rank' => $usrUnit->getRank(),
                'gradeLevel' => $usrUnit->getGradeLevel(),
                'isNewEncyclopedia' => $usrUnit->getIsNewEncyclopedia(),
                'lastRewardGradeLevel' => $usrUnit->getLastRewardGradeLevel(),
            ];
        }

        if ($isMulti) {
            $result['usrUnits'] = $response;
        } else {
            $result['usrUnit'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * Party
     */

    /**
     * @param array<mixed> $result
     * @param Collection<UsrPartyInterface> $usrParties
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrPartyData(array $result, Collection $usrParties, bool $isMulti): array
    {
        $response = [];
        foreach ($usrParties as $usrParty) {
            /** @var UsrPartyInterface $usrParty */
            $response[] = [
                'partyNo' => $usrParty->getPartyNo(),
                'partyName' => $usrParty->getPartyName(),
                'usrUnitId1' => $usrParty->getUsrUnitId1(),
                'usrUnitId2' => $usrParty->getUsrUnitId2(),
                'usrUnitId3' => $usrParty->getUsrUnitId3(),
                'usrUnitId4' => $usrParty->getUsrUnitId4(),
                'usrUnitId5' => $usrParty->getUsrUnitId5(),
                'usrUnitId6' => $usrParty->getUsrUnitId6(),
                'usrUnitId7' => $usrParty->getUsrUnitId7(),
                'usrUnitId8' => $usrParty->getUsrUnitId8(),
                'usrUnitId9' => $usrParty->getUsrUnitId9(),
                'usrUnitId10' => $usrParty->getUsrUnitId10(),
            ];
        }

        if ($isMulti) {
            $result['usrParties'] = $response;
        } else {
            $result['usrParty'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * 現状は一人一つの原画編成になる想定だが、拡張を想定して配列で返す形にしておく
     *
     * @param array<mixed> $result
     * @param Collection<UsrArtworkPartyEntity> $usrArtworkParties
     * @return array<mixed>
     */
    public function addUsrArtworkPartyData(
        array $result,
        Collection $usrArtworkParties,
        bool $isMulti,
    ): array {
        $response = [];
        foreach ($usrArtworkParties as $usrArtworkParty) {
            /** @var UsrArtworkPartyEntity $usrArtworkParty */
            $response[] = [
                'mstArtworkId1' => $usrArtworkParty->getMstArtworkId1(),
                'mstArtworkId2' => $usrArtworkParty->getMstArtworkId2(),
                'mstArtworkId3' => $usrArtworkParty->getMstArtworkId3(),
                'mstArtworkId4' => $usrArtworkParty->getMstArtworkId4(),
                'mstArtworkId5' => $usrArtworkParty->getMstArtworkId5(),
                'mstArtworkId6' => $usrArtworkParty->getMstArtworkId6(),
                'mstArtworkId7' => $usrArtworkParty->getMstArtworkId7(),
                'mstArtworkId8' => $usrArtworkParty->getMstArtworkId8(),
                'mstArtworkId9' => $usrArtworkParty->getMstArtworkId9(),
                'mstArtworkId10' => $usrArtworkParty->getMstArtworkId10(),
            ];
        }

        if ($isMulti) {
            $result['usrArtworkParties'] = $response;
        } else {
            $result['usrArtworkParty'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * Outpost
     */

    /**
     * @param array<mixed> $result
     * @param Collection $usrOutposts
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addUsrOutpostData(array $result, Collection $usrOutposts, bool $isMulti): array
    {
        $response = [];
        foreach ($usrOutposts as $usrOutpost) {
            /** @var UsrOutpostInterface $usrOutpost */
            $response[] = [
                'mstOutpostId' => $usrOutpost->getMstOutpostId(),
                'mstArtworkId' => $usrOutpost->getMstArtworkId(),
                'isUsed' => $usrOutpost->getIsUsed(),
            ];
        }

        if ($isMulti) {
            $result['usrOutposts'] = $response;
        } else {
            $result['usrOutpost'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrOutpostEnhancements
     * @return array<mixed>
     */
    public function addUsrOutpostEnhancementData(array $result, Collection $usrOutpostEnhancements): array
    {
        $response = [];
        foreach ($usrOutpostEnhancements as $usrOutpostEnhancement) {
            /** @var UsrOutpostEnhancementInterface $usrOutpostEnhancement */
            $response[] = [
                'mstOutpostId' => $usrOutpostEnhancement->getMstOutpostId(),
                'mstOutpostEnhancementId' => $usrOutpostEnhancement->getMstOutpostEnhancementId(),
                'level' => $usrOutpostEnhancement->getLevel(),
            ];
        }

        $result['usrOutpostEnhancements'] = $response;

        return $result;
    }

    /**
     * Mission
     */

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrMissionAchievementData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionAchievementId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionAchievements'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrMissionDailyData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionDailyId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionDailies'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrMissionWeeklyData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionWeeklyId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionWeeklies'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrMissionDailyBonusData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionDailyBonusId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionDailyBonuses'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrMissionBeginnerData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionBeginnerId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionBeginners'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param ?Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrEventMissionData(array $result, ?Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList ?? [] as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionEventId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionEvents'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param ?Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    private function addUsrEventDailyMissionData(array $result, ?Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList ?? [] as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionEventDailyId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionEventDailies'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrMissionStatusDataList
     * @return array<mixed>
     */
    public function addUsrLimitedTermMissionData(array $result, Collection $usrMissionStatusDataList): array
    {
        $response = [];
        foreach ($usrMissionStatusDataList as $usrMissionStatusData) {
            /** @var UsrMissionStatusData $usrMissionStatusData */
            $response[] = [
                'mstMissionLimitedTermId' => $usrMissionStatusData->getMstMissionId(),
                'progress' => $usrMissionStatusData->getProgress(),
                'isCleared' => $usrMissionStatusData->getIsCleared(),
                'isReceivedReward' => $usrMissionStatusData->getIsReceivedReward(),
            ];
        }

        $result['usrMissionLimitedTerms'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrMissionStatusData> $usrMissionEventStatusDataList
     * @param Collection<UsrMissionStatusData> $usrMissionEventDailyStatusDataList
     * @return array<mixed>
     */
    public function addEventMissionData(
        array $result,
        Collection $usrMissionEventStatusDataList,
        Collection $usrMissionEventDailyStatusDataList
    ): array {
        $usrMissionEventStatusDataList = $usrMissionEventStatusDataList
            ->groupBy(function ($usrMissionStatusData): string {
                return $usrMissionStatusData->getGroupId();
            });
        $usrMissionEventDailyStatusDataList = $usrMissionEventDailyStatusDataList
            ->groupBy(function ($usrMissionStatusData): string {
                return $usrMissionStatusData->getGroupId();
            });
        $mstEventIds = $usrMissionEventStatusDataList->keys()
            ->concat($usrMissionEventDailyStatusDataList->keys())
            ->unique();

        $result['missionEvents'] = [];
        foreach ($mstEventIds as $mstEventId) {
            $eventMission = [];
            $eventMission['mstEventId'] = $mstEventId;
            $eventMission = $this->addUsrEventMissionData(
                $eventMission,
                $usrMissionEventStatusDataList->get($mstEventId)
            );
            $eventMission = $this->addUsrEventDailyMissionData(
                $eventMission,
                $usrMissionEventDailyStatusDataList->get($mstEventId)
            );
            $result['missionEvents'][] = $eventMission;
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param int $daysFromStart
     * @return array<mixed>
     */
    public function addMissionBeginnerDaysFromStartData(array $result, int $daysFromStart): array
    {
        $result['missionBeginnerDaysFromStart'] = $daysFromStart;
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UsrUserBuyCountInterface|null $usrUserBuyCount
     * @return array<mixed>
     */
    public function addUsrBuyCountData(array $result, ?UsrUserBuyCountInterface $usrUserBuyCount): array
    {
        $result['usrBuyCount'] = [
            'dailyBuyStaminaAdCount' => $usrUserBuyCount?->getDailyBuyStaminaAdCount() ?? 0,
            'dailyBuyStaminaAdAt' => StringUtil::convertToISO8601($usrUserBuyCount?->getDailyBuyStaminaAdAt()),
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MissionReward> $rewards
     * @return array<mixed>
     */
    public function addMissionRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var MissionReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['missionRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MissionDailyBonusReward> $missionDailyBonusRewards
     * @return array<mixed>
     */
    public function addMissionDailyBonusRewardData(array $result, Collection $missionDailyBonusRewards): array
    {
        $response = [];
        foreach ($missionDailyBonusRewards as $reward) {
            /** @var MissionDailyBonusReward $reward */
            $response[] = array_merge(
                $reward->getRewardResponseData(),
                [
                    'missionType' => $reward->getDailyBonusType(),
                    'loginDayCount' => $reward->getLoginDayCount(),
                ]
            );
        }

        $result['dailyBonusRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MissionEventDailyBonusReward> $missionEventDailyBonusRewards
     * @return array<mixed>
     */
    public function addMissionEventDailyBonusRewardData(array $result, Collection $missionEventDailyBonusRewards): array
    {
        $response = [];
        foreach ($missionEventDailyBonusRewards as $reward) {
            /** @var MissionEventDailyBonusReward $reward */
            $response[] = array_merge(
                $reward->getRewardResponseData(),
                [
                    'mstMissionEventDailyBonusScheduleId' => $reward->getMstMissionEventDailyBonusScheduleId(),
                    'loginDayCount' => $reward->getLoginDayCount(),
                ]
            );
        }

        $result['eventDailyBonusRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrEventDailyBonusProgresses
     * @return array<mixed>
     */
    public function addUsrEventDailyBonusProgressData(array $result, Collection $usrEventDailyBonusProgresses): array
    {
        $response = [];
        foreach ($usrEventDailyBonusProgresses as $progress) {
            /** @var UsrMissionEventDailyBonusProgressInterface $progress */
            $response[] = [
                'mstMissionEventDailyBonusScheduleId' => $progress->getMstMissionEventDailyBonusScheduleId(),
                'progress' => $progress->getProgress(),
            ];
        }

        $result['usrMissionEventDailyBonusProgresses'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MissionReceiveRewardStatus> $receiveRewardStatuses
     * @return array<mixed>
     */
    public function addMissionReceiveRewardData(array $result, Collection $receiveRewardStatuses): array
    {
        $response = [];

        foreach ($receiveRewardStatuses as $receiveRewardStatus) {
            /** @var MissionReceiveRewardStatus $receiveRewardStatus */
            $response[] = [
                'missionType' => $receiveRewardStatus->getMissionType(),
                'mstMissionId' => $receiveRewardStatus->getMstMissionId(),
                'unreceivedRewardReason' => $receiveRewardStatus->getUnreceivedReason(),
            ];
        }

        $result['missionReceiveRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<UsrMissionBonusPointData> $usrMissionBonusPoints
     * @return array<mixed>
     */
    public function addUsrMissionBonusPointData(array $result, Collection $usrMissionBonusPoints): array
    {
        $response = [];

        foreach ($usrMissionBonusPoints as $usrMissionBonusPoint) {
            /** @var UsrMissionBonusPointData $usrMissionBonusPoint */
            $response[] = [
                'missionType' => $usrMissionBonusPoint->getMissionType(),
                'point' => $usrMissionBonusPoint->getPoint(),
                'receivedRewardPoints' => $usrMissionBonusPoint->getReceivedRewardPoints()->values()->toArray(),
            ];
        }

        $result['usrMissionBonusPoints'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrComebackBonusProgresses
     * @return array<mixed>
     */
    public function addUsrComebackBonusProgressData(array $result, Collection $usrComebackBonusProgresses): array
    {
        $response = [];
        foreach ($usrComebackBonusProgresses as $progress) {
            /** @var UsrComebackBonusProgressInterface $progress */
            $response[] = [
                'mstComebackBonusScheduleId' => $progress->getMstScheduleId(),
                'progress' => $progress->getProgress(),
                'startAt' => StringUtil::convertToISO8601($progress->getStartAt()),
                'endAt' => StringUtil::convertToISO8601($progress->getEndAt()),
            ];
        }

        $result['usrComebackBonusProgresses'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<ComebackBonusReward> $comebackBonusRewards
     * @return array<mixed>
     */
    public function addComebackBonusRewardData(array $result, Collection $comebackBonusRewards): array
    {
        $response = [];
        foreach ($comebackBonusRewards as $reward) {
            /** @var ComebackBonusReward $reward */
            $response[] = array_merge(
                [
                    'mstComebackBonusScheduleId' => $reward->getMstComebackBonusScheduleId(),
                    'loginDayCount' => $reward->getLoginDayCount(),
                ],
                $reward->getRewardResponseData(),
            );
        }

        $result['comebackBonusRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<ItemReward>|Collection<ItemTradeReward> $rewards
     * @return array<mixed>
     */
    public function addItemRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var ItemReward|ItemTradeReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['itemRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<\App\Domain\Resource\Entities\Rewards\UnitGradeUpReward> $rewards
     * @return array<mixed>
     */
    public function addUnitGradeUpRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            $response[] = $reward->getRewardResponseData();
        }

        $result['unitGradeUpRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UsrStageStatusData $usrStageStatusData
     * @return array<mixed>
     */
    public function addContinueCountData(array $result, UsrStageStatusData $usrStageStatusData): array
    {
        $result['continueCount'] = $usrStageStatusData->getContinueCount();

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UsrStageStatusData $usrStageStatusData
     * @return array<mixed>
     */
    public function addContinueAdCountData(array $result, UsrStageStatusData $usrStageStatusData): array
    {
        $result['continueAdCount'] = $usrStageStatusData->getContinueAdCount();

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param UsrInGameStatusData $usrInGameStatus
     * @return array<mixed>
     */
    public function addUsrInGameStatusData(array $result, UsrInGameStatusData $usrInGameStatus): array
    {
        $result['usrInGameStatus'] = [
            'isStartedSession' => $usrInGameStatus->getIsStartedSession(),
            'inGameContentType' => $usrInGameStatus->getInGameContentType(),
            'targetMstId' => $usrInGameStatus->getTargetMstId(),
            'partyNo' => $usrInGameStatus->getPartyNo(),
            'continueCount' => $usrInGameStatus->getContinueCount(),
            'continueAdCount' => $usrInGameStatus->getContinueAdCount(),
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<BaseReward> $rewards
     * @return array<mixed>
     */
    public function addDuplicatedRewardData(array $result, Collection $rewards): array
    {
        $emblemDuplicate = false;
        foreach ($rewards as $reward) {
            if ($reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_EMBLEM) {
                $emblemDuplicate = true;
                break;
            }
        }

        $result['isEmblemDuplicated'] = $emblemDuplicate;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param MissionStatusData $missionStatusData
     * @return array<mixed>
     */
    public function addMissionStatusData(array $result, MissionStatusData $missionStatusData): array
    {
        $result['missionStatus'] = [
            'isBeginnerMissionCompleted' => $missionStatusData->isBeginnerMissionCompleted,
        ];

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<Message> $messages
     * @return array<mixed>
     */
    public function addMessageData(array $result, Collection $messages): array
    {
        $response = [];
        /** @var Message $message */
        foreach ($messages as $message) {
            $messageRewards = [];
            if ($message->getMessageRewards() !== null) {
                /** @var MessageReward $reward */
                foreach ($message->getMessageRewards() as $reward) {
                    $messageRewards[] = $reward->getRewardResponseData();
                }
            }
            $response[] = [
                'usrMessageId' => $message->getUsrMessageId(),
                'oprMessageId' => $message->getMngMessageId(),
                'startAt' => StringUtil::convertToISO8601($message->getStartAt()),
                'openedAt' => StringUtil::convertToISO8601($message->getOpenedAt()),
                'receivedAt' => StringUtil::convertToISO8601($message->getReceivedAt()),
                'expiredAt' => StringUtil::convertToISO8601($message->getExpiredAt()),
                'messageRewards' => $messageRewards,
                'title' => $message->getTitle(),
                'body' => $message->getBody(),
            ];
        }

        $result['messages'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<MessageReward> $rewards
     * @return array<mixed>
     */
    public function addMessageRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var MessageReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['messageRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrEmblems
     * @return array<mixed>
     */
    public function addUsrEmblemData(array $result, Collection $usrEmblems): array
    {
        $response = [];
        foreach ($usrEmblems as $usrEmblem) {
            $response[] = [
                'mstEmblemId' => $usrEmblem->getMstEmblemId(),
                'isNewEncyclopedia' => $usrEmblem->getIsNewEncyclopedia(),
            ];
        }
        $result['usrEmblems'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrArtworks
     * @return array<mixed>
     */
    public function addUsrArtworkData(array $result, Collection $usrArtworks, bool $isMulti = true): array
    {
        $response = [];
        if (! $isMulti) {
            $usrArtworks = $usrArtworks->take(1);
        }

        foreach ($usrArtworks as $usrArtwork) {
            $response[] = [
                'mstArtworkId' => $usrArtwork->getMstArtworkId(),
                'isNewEncyclopedia' => $usrArtwork->getIsNewEncyclopedia(),
                'gradeLevel' => $usrArtwork->getGradeLevel(),
            ];
        }

        if ($isMulti) {
            $result['usrArtworks'] = $response;
        } else {
            $result['usrArtwork'] = count($response) > 0 ? $response[0] : [];
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $usrArtworkFragments
     * @return array<mixed>
     */
    public function addUsrArtworkFragmentData(array $result, Collection $usrArtworkFragments): array
    {
        $response = [];
        foreach ($usrArtworkFragments as $usrArtworkFragment) {
            $response[] = [
                'mstArtworkId' => $usrArtworkFragment->getMstArtworkId(),
                'mstArtworkFragmentId' => $usrArtworkFragment->getMstArtworkFragmentId(),
            ];
        }
        $result['usrArtworkFragments'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection   $usrReceivedUnitEncyclopediaRewards
     * @return array<mixed>
     */
    public function addUsrReceivedUnitEncyclopediaRewardData(array $result, Collection $usrReceivedUnitEncyclopediaRewards): array
    {
        $response = [];
        foreach ($usrReceivedUnitEncyclopediaRewards as $reward) {
            $response[] = [
                'mstUnitEncyclopediaRewardId' => $reward->getMstUnitEncyclopediaRewardId(),
            ];
        }
        $result['usrReceivedUnitEncyclopediaRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection   $rewards
     * @return array<mixed>
     */
    public function addEncyclopediaFirstCollectionRewardData(array $result, Collection $rewards): array
    {
        $response = [];
        foreach ($rewards as $reward) {
            /** @var EncyclopediaFirstCollectionReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['encyclopediaFirstCollectionRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection $rewards
     * @return array<mixed>
     */
    public function addUnitEncyclopediaRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var UnitEncyclopediaReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['unitEncyclopediaRewards'] = $response;

        return $result;
    }

    /**
     * Campaign
     */

    /**
     * @param array<mixed> $result
     * @param Collection<OprCampaignData> $oprCampaignDataList
     * @return array<mixed>
     */
    public function addOprCampaignData(array $result, Collection $oprCampaignDataList): array
    {
        $response = [];
        foreach ($oprCampaignDataList as $oprCampaignData) {
            $response[] = $oprCampaignData->formatToResponse();
        }
        $result['oprCampaigns'] = $response;

        return $result;
    }

    /**
     * Gacha
     */

    /**
     * @param array<mixed> $result
     * @param Collection<GachaReward> $rewards
     * @return array<mixed>
     */
    public function addGachaResultData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var GachaReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['gachaResults'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<StepUpGachaStepReward> $stepRewards
     * @return array<mixed>
     */
    public function addStepRewardsData(array $result, Collection $stepRewards): array
    {
        $response = [];

        foreach ($stepRewards as $reward) {
            /** @var StepUpGachaStepReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        if (!empty($response)) {
            $result['stepRewards'] = $response;
        }

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addPvpHeldStatusData(array $result, PvpHeldStatusData $pvpHeldStatusData): array
    {
        $result['pvpHeldStatus'] =  $pvpHeldStatusData->formatToResponse();
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addUsrPvpStatusData(array $result, UsrPvpStatusData $usrPvpStatusData): array
    {
        $result['usrPvpStatus'] = $usrPvpStatusData->formatToResponse();
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addPvpEndResultBonusPointData(
        array $result,
        PvpResultPoints $pvpResultPoints
    ): array {
        $result['pvpEndResultBonusPoint'] = $pvpResultPoints->formatToResponse();
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<PvpTotalScoreReward> $rewards
     * @return array<mixed>
     */
    public function addPvpTotalScoreRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var PvpTotalScoreReward $reward */
            $response[] = $reward->formatToResponse();
        }

        $result['pvpTotalScoreRewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<OpponentSelectStatusResponseData> $opponentSelectStatusResponses
     * @param bool $isMulti
     * @return array<mixed>
     */
    public function addOpponentSelectStatusData(array $result, Collection $opponentSelectStatusResponses, bool $isMulti = false): array
    {
        $response = [];
        /** @var OpponentSelectStatusResponseData $opponentSelectStatusResponse */
        foreach ($opponentSelectStatusResponses as $opponentSelectStatusResponse) {
            $response[] = $opponentSelectStatusResponse->formatToResponse();
        }
        if ($isMulti) {
            $result['opponentSelectStatuses'] = $response;
        } else {
            $result['opponentSelectStatus'] = count($response) > 0 ? $response[0] : [];
        }
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addPvpPreviousSeasonResultData(
        array $result,
        ?PvpPreviousSeasonResultData $pvpPreviousSeasonResultData
    ): array {
        if ($pvpPreviousSeasonResultData === null) {
            $result['pvpPreviousSeasonResult'] = null;
            return $result;
        }
        $result['pvpPreviousSeasonResult'] = $pvpPreviousSeasonResultData->formatToResponse();

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @return array<mixed>
     */
    public function addIsViewableRanking(
        array $result,
        bool $isViewableRanking
    ): array {
        $result['isViewableRanking'] = $isViewableRanking;
        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param OpponentPvpStatusData $opponentPvpStatusData
     * @return array<mixed>
     */
    public function addOpponentPvpStatusData(
        array $result,
        OpponentPvpStatusData $opponentPvpStatusData
    ): array {
        $response = $opponentPvpStatusData->formatToResponse();
        $response = $this->addUsrOutpostEnhancementData($response, $opponentPvpStatusData->getUsrOutpostEnhancements());
        $result['opponentPvpStatus'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<BaseReward> $rewards
     * @return array<mixed>
     */
    public function addShopPurchaseRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var BaseReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['rewards'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<\App\Domain\Resource\Mng\Entities\MngContentCloseEntity> $mngContentCloses
     * @return array<mixed>
     */
    public function addMngContentCloseData(array $result, Collection $mngContentCloses): array
    {
        $response = [];

        foreach ($mngContentCloses as $contentClose) {
            $response[] = [
                'id' => $contentClose->getId(),
                'contentType' => $contentClose->getContentType(),
                'contentId' => $contentClose->getContentId(),
                'startAt' => StringUtil::convertToISO8601($contentClose->getStartAt()->toDateTimeString()),
                'endAt' => StringUtil::convertToISO8601($contentClose->getEndAt()->toDateTimeString()),
                'isValid' => $contentClose->getIsValid() === 1,
            ];
        }

        $result['mngContentCloses'] = $response;

        return $result;
    }

    /**
     * @param array<mixed> $result
     * @param Collection<\App\Domain\Gacha\Entities\GachaHistory> $gachaHistories
     * @return array<mixed>
     */
    public function addGachaHistoryData(array $result, Collection $gachaHistories): array
    {
        $result['gachaHistories'] = $gachaHistories->map(fn($history) => $history->formatToResponse());
        return $result;
    }

    /**
     * usrExchangeLineupsをレスポンスに追加
     *
     * @param array<mixed> $result
     * @param Collection<UsrExchangeLineupInterface> $usrExchangeLineups
     * @return array<mixed>
     */
    public function addUsrExchangeLineupData(array $result, Collection $usrExchangeLineups): array
    {
        $result['usrExchangeLineups'] = $usrExchangeLineups->map(function ($lineup) {
            /** @var \App\Domain\Exchange\Models\UsrExchangeLineupInterface $lineup */
            return [
                'mstExchangeId' => $lineup->getMstExchangeId(),
                'mstExchangeLineupId' => $lineup->getMstExchangeLineupId(),
                'tradeCount' => $lineup->getTradeCount(),
            ];
        })->values()->toArray();

        return $result;
    }

    /**
     * WebStore購入商品IDをレスポンスに追加
     *
     * @param array<mixed> $result
     * @param Collection<string> $unnotifiedProductSubIds 未通知のWebStore購入商品ID(product_sub_id)のリスト
     * @return array<mixed>
     */
    public function addWebStorePurchaseProductSubIds(array $result, Collection $unnotifiedProductSubIds): array
    {
        $result['webstorePurchaseProductSubIds'] = $unnotifiedProductSubIds->all();

        return $result;
    }

    /**
     * exchangeTradeRewardsをレスポンスに追加
     *
     * @param array<mixed> $result
     * @param Collection<ExchangeTradeReward> $rewards
     * @return array<mixed>
     */
    public function addExchangeTradeRewardData(array $result, Collection $rewards): array
    {
        $response = [];

        foreach ($rewards as $reward) {
            /** @var ExchangeTradeReward $reward */
            $response[] = $reward->getRewardResponseData();
        }

        $result['exchangeRewards'] = $response;

        return $result;
    }

    /**
     * @param Collection<string, int> $countMap
     * @return array<mixed>
     */
    private function makeMissionEventRewardCountData(Collection $countMap): array
    {
        $result = [];
        foreach ($countMap as $mstEventId => $count) {
            $result[] = [
                'mstEventId' => $mstEventId,
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * @param Collection<string, int> $countMap key: mstArtworkPanelMissionId, value: count
     * @return array<array{mstArtworkPanelMissionId: string, unreceivedMissionRewardCount: int}>
     */
    private function makeMissionArtworkPanelRewardCountData(Collection $countMap): array
    {
        $result = [];
        foreach ($countMap as $mstArtworkPanelMissionId => $count) {
            $result[] = [
                'mstArtworkPanelMissionId' => $mstArtworkPanelMissionId,
                'unreceivedMissionRewardCount' => $count,
            ];
        }
        return $result;
    }
}
