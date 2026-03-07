<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface;
use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaRewardInterface;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\InGame\Models\UsrEnemyDiscoveryInterface;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Item\Models\UsrItemTradeInterface;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface;
use App\Domain\Outpost\Models\UsrOutpostEnhancementInterface;
use App\Domain\Outpost\Models\UsrOutpostInterface;
use App\Domain\Party\Models\UsrArtworkPartyInterface;
use App\Domain\Party\Models\UsrPartyInterface;
use App\Domain\Resource\Entities\Rewards\ComebackBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Mng\Entities\MngContentCloseEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Shop\Entities\UsrStoreInfoEntity;
use App\Domain\Shop\Models\UsrConditionPackInterface;
use App\Domain\Shop\Models\UsrShopItemInterface;
use App\Domain\Shop\Models\UsrShopPassInterface;
use App\Domain\Shop\Models\UsrStoreProductInterface;
use App\Domain\Shop\Models\UsrTradePackInterface;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\User\Models\UsrUserInterface as UsrUser;
use App\Domain\User\Models\UsrUserLoginInterface;
use App\Domain\User\Models\UsrUserProfileInterface as UsrUserProfile;
use App\Http\Responses\Data\UsrPvpStatusData;
use Illuminate\Support\Collection;

class GameFetchOtherData
{
    /**
     * @param UsrUser $usrUser
     * @param UsrUserProfile $usrUserProfile
     * @param Collection<UsrUnitInterface> $usrUnits
     * @param Collection<UsrItemInterface> $usrItems
     * @param Collection<OprProductEntity> $oprProducts
     * @param Collection<UsrStoreProductInterface> $usrStoreProducts
     * @param Collection<UsrShopItemInterface> $usrShopItems
     * @param Collection<UsrTradePackInterface> $usrTradePacks
     * @param Collection<UsrConditionPackInterface> $usrConditionPacks
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @param Collection<UsrPartyInterface> $usrParties
     * @param UsrArtworkPartyInterface $usrArtworkParty
     * @param Collection<UsrOutpostInterface> $usrOutposts
     * @param Collection<UsrOutpostEnhancementInterface> $usrOutpostEnhancements
     * @param UsrInGameStatusData $usrInGameStatus
     * @param Collection<MissionDailyBonusReward> $missionDailyBonusRewards
     * @param Collection<UsrMissionEventDailyBonusProgressInterface> $usrEventDailyBonusProgresses @codingStandardsIgnoreLine
     * @param Collection<MissionEventDailyBonusReward> $missionEventDailyBonusRewards
     * @param Collection<UsrComebackBonusProgressInterface> $usrComebackBonusProgresses
     * @param Collection<ComebackBonusReward> $comebackBonusRewards
     * @param Collection<UsrEmblemInterface> $usrEmblems
     * @param Collection<UsrArtworkInterface> $usrArtworks
     * @param Collection<UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param Collection<UsrReceivedUnitEncyclopediaRewardInterface> $usrReceivedUnitEncyclopediaRewards @codingStandardsIgnoreLine
     * @param Collection<MngInGameNoticeData> $mngInGameNoticeDataList
     * @param UsrStoreInfoEntity|null $usrStoreInfoData
     * @param Collection<OprCampaignData> $oprCampaignDataList
     * @param Collection<UsrGachaInterface> $usrGachas
     * @param Collection<UsrGachaUpperInterface> $usrGachaUppers
     * @param Collection<UsrShopPassInterface> $usrShopPasses
     * @param Collection<UsrItemTradeInterface> $usrItemTrades
     * @param Collection<UsrEnemyDiscoveryInterface> $usrEnemyDiscoveries
     * @param AdventBattleRaidTotalScoreData|null $adventBattleRaidTotalScoreData
     * @param Collection<UsrTutorialData> $freePartUsrTutorialDataList
     * @param string|null $bnidLinkedAt
     * @param SysPvpSeasonEntity $sysPvpSeasonEntity
     * @param UsrPvpStatusData $usrPvpStatusData
     * @param Collection<MngContentCloseEntity> $mngContentCloses
     * @param Collection<UsrExchangeLineupInterface> $usrExchangeLineups
     * @param Collection<string> $unnotifiedProductSubIds 未通知のWebStore購入商品ID(product_sub_id)のリスト
     */
    public function __construct(
        public UsrUser $usrUser,
        public UsrUserProfile $usrUserProfile,
        public Collection $usrUnits,
        public Collection $usrItems,
        public Collection $oprProducts,
        public Collection $usrStoreProducts,
        public Collection $usrShopItems,
        public Collection $usrTradePacks,
        public Collection $usrConditionPacks,
        public UsrIdleIncentiveInterface $usrIdleIncentive,
        public Collection $usrParties,
        public UsrArtworkPartyInterface $usrArtworkParty,
        public Collection $usrOutposts,
        public Collection $usrOutpostEnhancements,
        public UsrInGameStatusData $usrInGameStatus,
        public ?UsrUserLoginInterface $usrUserLogin,
        public Collection $missionDailyBonusRewards,
        public Collection $usrEventDailyBonusProgresses,
        public Collection $missionEventDailyBonusRewards,
        public Collection $usrComebackBonusProgresses,
        public Collection $comebackBonusRewards,
        public Collection $usrEmblems,
        public Collection $usrArtworks,
        public Collection $usrArtworkFragments,
        public Collection $usrReceivedUnitEncyclopediaRewards,
        public Collection $mngInGameNoticeDataList,
        public ?UsrStoreInfoEntity $usrStoreInfoData,
        public Collection $oprCampaignDataList,
        public Collection $usrGachas,
        public Collection $usrGachaUppers,
        public Collection $usrShopPasses,
        public Collection $usrItemTrades,
        public Collection $usrEnemyDiscoveries,
        public ?AdventBattleRaidTotalScoreData $adventBattleRaidTotalScoreData,
        public Collection $freePartUsrTutorialDataList,
        public ?string $bnidLinkedAt,
        public SysPvpSeasonEntity $sysPvpSeasonEntity,
        public UsrPvpStatusData $usrPvpStatusData,
        public Collection $mngContentCloses,
        public Collection $usrExchangeLineups,
        public Collection $unnotifiedProductSubIds,
    ) {
    }
}
