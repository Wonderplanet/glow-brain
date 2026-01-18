using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.Translators.Pvp;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class GameFetchOtherTranslator
    {
        public static GameFetchOtherModel TranslateToModel(GameFetchOtherData gameFetchOtherData)
        {
            var tutorialStatusData = gameFetchOtherData.TutorialStatus;
            var tutorialStatusModel = TutorialStatusDataTranslator.ToTutorialStatusModel(tutorialStatusData);

            var usrTutorialData = gameFetchOtherData.UsrTutorialFreeParts;
            var usrTutorialModels = usrTutorialData
                .Select(UserTutorialDataTranslator.ToUserTutorialFreePartModel)
                .ToArray();

            var userProfileData = gameFetchOtherData.UsrProfile;
            var userProfileModel = UserProfileDataTranslator.ToUserProfileModel(userProfileData);

            var unitData = gameFetchOtherData.UsrUnits;
            var userUnitModels = unitData
                    .Select(UserUnitDataTranslator.ToUserUnitModel)
                    .ToList();

            var userItemModels = gameFetchOtherData.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var userLoginData = gameFetchOtherData.UsrLogin;
            var userLoginInfoModel = new UserLoginInfoModel(
                userLoginData.LastLoginAt,
                new LoginDayCount(userLoginData.LoginDayCount),
                new LoginDayCount(userLoginData.LoginContinueDayCount));

            var shopItemData = gameFetchOtherData.UsrShopItems;
            var userShopItemModels = shopItemData
                .Select(data => new UserShopItemModel(
                    new MasterDataId(data.MstShopItemId),
                    new ShopItemTradeCount(data.TradeCount),
                    new ShopItemTradeCount(data.TradeTotalCount)))
                .ToList();

            var storeProductData = gameFetchOtherData.UsrStoreProducts;
            var userStoreProductModels = storeProductData
                .Select(data => new UserStoreProductModel(
                    new MasterDataId(data.ProductSubId),
                    new PurchaseCount(data.PurchaseCount),
                    new PurchaseCount(data.PurchaseTotalCount)))
                .ToList();

            var usrConditionPacks = gameFetchOtherData.UsrConditionPacks;
            var userConditionPackModels = usrConditionPacks != null
                ? usrConditionPacks
                    .Select(UserConditionPackDataTranslator.ToModel)
                    .ToList()
                : new List<UserConditionPackModel>();

            var idleIncentiveData = gameFetchOtherData.UsrIdleIncentive;
            var idleIncentiveModel = UserIdleIncentiveDataTranslator.ToModel(idleIncentiveData);

            var userPartyModels = gameFetchOtherData.UsrParties
                .Select(UserPartyDataTranslator.TranslateToModel)
                .ToList();

            var userOutpostModels = gameFetchOtherData.UsrOutposts
                .Select(UserOutpostDataTranslator.TranslateToModel)
                .ToList();

            var userOutpostEnhanceModels = gameFetchOtherData.UsrOutpostEnhancements
                .Select(UserOutpostEnhancementDataTranslator.TranslateToModel)
                .ToList();

            var statusModel =UserInGameStatusDataTranslator.ToUserInGameStatusModel(gameFetchOtherData.UsrInGameStatus);

            var dailyBonus = gameFetchOtherData.DailyBonusRewards
                .Select(data => new MissionReceivedDailyBonusModel(
                    data.MissionType,
                    new LoginDayCount(data.LoginDayCount),
                    RewardDataTranslator.Translate(data.Reward)))
                .ToList();

            var eventDailyBonusRewardModels = gameFetchOtherData.EventDailyBonusRewards
                ?.Select(MissionEventDailyBonusUpdateResultModelTranslator.ToMissionEventDailyBonusRewardModel)
                .ToList()
                ?? new List<MissionEventDailyBonusRewardModel>();

            var eventDailyBonusProgressModels = gameFetchOtherData.UsrMissionEventDailyBonusProgresses
                ?.Select(data => new UserMissionEventDailyBonusProgressModel(
                    new MasterDataId(data.MstMissionEventDailyBonusScheduleId),
                    new LoginDayCount(data.Progress)))
                .ToList()
                ?? new List<UserMissionEventDailyBonusProgressModel>();

            var comebackBonusRewardModels = gameFetchOtherData.ComebackBonusRewards
                ?.Select(ComebackBonusRewardDataTranslator.ToComebackBonusRewardModel)
                .ToList()
                ?? new List<ComebackBonusRewardModel>();

            var comebackBonusProgressModels = gameFetchOtherData.UsrComebackBonusProgresses
                ?.Select(UserComebackBonusProgressDataTranslator.ToUserComebackBonusProgressModel)
                .ToList()
                ?? new List<UserComebackBonusProgressModel>();

            var emblem = gameFetchOtherData.UsrEmblems
                ?.Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList()
                ?? new List<UserEmblemModel>();

            var artworkModels = gameFetchOtherData.UsrArtworks
                ?.Select(UserArtworkDataTranslator.ToUserArtworkModel)
                .ToList()
                ?? new List<UserArtworkModel>();

            var artworkFragmentModels = gameFetchOtherData.UsrArtworkFragments
                ?.Select(UserArtworkFragmentDataTranslator.ToUserArtworkFragmentModel)
                .ToList()
                ?? new List<UserArtworkFragmentModel>();

            var gachaModels = gameFetchOtherData.UsrGachas
                ?.Select(UserGachaDataTranslator.ToUserGachaModel)
                .ToList()
                ?? new List<UserGachaModel>();

            var gachaUpperModels = gameFetchOtherData.UsrGachaUppers
                ?.Select(UserGachaUpperDataTranslator.ToUserDrawCountThresholdModel)
                .ToList()
                ?? new List<UserDrawCountThresholdModel>();

            var userReceivedUnitEncyclopediaRewardModels = gameFetchOtherData.UsrReceivedUnitEncyclopediaRewards
                ?.Select(UserReceivedUnitEncyclopediaRewardDataTranslator.TranslateToModel)
                .ToList()
                ?? new List<UserReceivedUnitEncyclopediaRewardModel>();

            var oprInGameNoticeModels = gameFetchOtherData.OprInGameNotices
                ?.Select(OprInGameNoticeDataTranslator.ToOprInGameNoticeModel)
                .ToList()
                ?? new List<OprNoticeModel>();

            var userStoreInfoModel = UserStoreInfoModelTranslator.ToUserStoreInfoModel(gameFetchOtherData.UsrStoreInfo);

            var userItemTradeModels = gameFetchOtherData.UsrItemTrades
                                          ?.Select(UserItemTradeModelTranslator.ToUserItemTradeModel)
                                          .ToList()
                                      ?? new List<UserItemTradeModel>();

            var userDiscoverEnemyModels = gameFetchOtherData.UsrEnemyDiscoveries
                ?.Select(UserEnemyDiscoverDataTranslator.Translate)
                .ToList()
                ?? new List<UserEnemyDiscoverModel>();

            var adventBattleRaidTotalScoreModel = gameFetchOtherData.AdventBattleRaidTotalScore != null
                ? new AdventBattleRaidTotalScoreModel(
                    new MasterDataId(gameFetchOtherData.AdventBattleRaidTotalScore.MstAdventBattleId),
                    new AdventBattleRaidTotalScore(gameFetchOtherData.AdventBattleRaidTotalScore.TotalDamage))
                : AdventBattleRaidTotalScoreModel.Empty;

            var userShopPassModels = gameFetchOtherData.UsrShopPasses
                ?.Select(UserShopPassDataTranslator.ToUserShopPassModel)
                .ToList()
                ?? new List<UserShopPassModel>();

            var bnIdLinkedAt = gameFetchOtherData.BnidLinkedAt;
            var gameStartAt = gameFetchOtherData.GameStartAt;

            var sysPvpSeason = gameFetchOtherData.SysPvpSeason != null
                ? PvpDataTranslator.ToSysPvpSeasonModel(gameFetchOtherData.SysPvpSeason)
                : SysPvpSeasonModel.Empty;

            var userPvpStatusModel = gameFetchOtherData.UsrPvpStatus != null
                ? PvpDataTranslator.ToUserPvpStatusModel(gameFetchOtherData.UsrPvpStatus)
                : UserPvpStatusModel.Empty;

            var userTradePackModels = gameFetchOtherData.UsrTradePacks
                ?.Select(UserTradePackDataTranslator.Translate)
                .ToList()
                ?? new List<UserTradePackModel>();

            var mngContentCloseModels = gameFetchOtherData.MngContentCloses
                                            ?.Select(MngContentCloseDataTranslator.ToMngContentCloseModel)
                                            .ToList()
                                        ?? new List<MngContentCloseModel>();

            var userExchangeLineupModels = gameFetchOtherData.UsrExchangeLineups
                ?.Select(UsrExchangeLineupDataTranslator.Translate)
                .ToList()
                ?? new List<UserExchangeLineupModel>();

            return new GameFetchOtherModel(
                tutorialStatusModel,
                usrTutorialModels,
                userProfileModel,
                userLoginInfoModel,
                userUnitModels,
                userItemModels,
                userShopItemModels,
                userStoreProductModels,
                userConditionPackModels,
                idleIncentiveModel,
                userPartyModels,
                userOutpostModels,
                userOutpostEnhanceModels,
                statusModel,
                dailyBonus,
                eventDailyBonusRewardModels,
                eventDailyBonusProgressModels,
                comebackBonusRewardModels,
                comebackBonusProgressModels,
                emblem,
                artworkModels,
                artworkFragmentModels,
                gachaModels,
                gachaUpperModels,
                userReceivedUnitEncyclopediaRewardModels,
                oprInGameNoticeModels,
                userStoreInfoModel,
                userItemTradeModels,
                userDiscoverEnemyModels,
                adventBattleRaidTotalScoreModel,
                userShopPassModels,
                userTradePackModels,
                bnIdLinkedAt,
                gameStartAt,
                sysPvpSeason,
                userPvpStatusModel,
                mngContentCloseModels,
                userExchangeLineupModels);
        }
    }
}
