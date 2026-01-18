using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models
{
    public record GameFetchOtherModel(
        TutorialStatusModel TutorialStatus,
        IReadOnlyList<UserTutorialFreePartModel> UserTutorialFreePartModels,
        UserProfileModel UserProfileModel,
        UserLoginInfoModel UserLoginInfoModel,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserShopItemModel> UserShopItemModels,
        IReadOnlyList<UserStoreProductModel> UserStoreProductModels,
        IReadOnlyList<UserConditionPackModel> UserConditionPackModels,
        UserIdleIncentiveModel UserIdleIncentiveModel,
        IReadOnlyList<UserPartyModel> UserPartyModels,
        IReadOnlyList<UserHomeOutpostModel> UserOutpostModels,
        IReadOnlyList<UserOutpostEnhanceModel> UserOutpostEnhanceModels,
        UserInGameStatusModel UserInGameStatusModel,
        IReadOnlyList<MissionReceivedDailyBonusModel> MissionReceivedDailyBonusModel,
        IReadOnlyList<MissionEventDailyBonusRewardModel> MissionEventDailyBonusRewardModels,
        IReadOnlyList<UserMissionEventDailyBonusProgressModel> UserMissionEventDailyBonusProgressModels,
        IReadOnlyList<ComebackBonusRewardModel> ComebackBonusRewardModels,
        IReadOnlyList<UserComebackBonusProgressModel> UserComebackBonusProgressModels,
        IReadOnlyList<UserEmblemModel> UserEmblemModel,
        IReadOnlyList<UserArtworkModel> UserArtworkModels,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragmentModels,
        IReadOnlyList<UserGachaModel> UserGachaModels,
        IReadOnlyList<UserDrawCountThresholdModel> UserGachaDrawCountThresholdModels,
        IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> UserReceivedUnitEncyclopediaRewardModels,
        IReadOnlyList<OprNoticeModel> OprInGameNoticeModels,
        UserStoreInfoModel UserStoreInfoModel,
        IReadOnlyList<UserItemTradeModel> UserItemTradeModels,
        IReadOnlyList<UserEnemyDiscoverModel> UserEnemyDiscoverModels,
        AdventBattleRaidTotalScoreModel AdventBattleRaidTotalScoreModel,
        IReadOnlyList<UserShopPassModel> UserShopPassModels,
        IReadOnlyList<UserTradePackModel> UserTradePackModels,
        DateTimeOffset? BnIdLinkedAt,
        DateTimeOffset GameStartAt,
        SysPvpSeasonModel SysPvpSeasonModel,
        UserPvpStatusModel UserPvpStatusModel, /* 最新版は基本的にPvpTopResultModelから取得する */
        IReadOnlyList<MngContentCloseModel> MngContentCloseModels,
        IReadOnlyList<UserExchangeLineupModel> UsrExchangeLineupModels
        )
    {
        public static GameFetchOtherModel Empty { get; } = new GameFetchOtherModel(
            TutorialStatusModel.Empty,
            new List<UserTutorialFreePartModel>(),
            UserProfileModel.Empty,
            UserLoginInfoModel.Empty,
            new List<UserUnitModel>(),
            new List<UserItemModel>(),
            new List<UserShopItemModel>(),
            new List<UserStoreProductModel>(),
            new List<UserConditionPackModel>(),
            UserIdleIncentiveModel.Empty,
            new List<UserPartyModel>(),
            new List<UserHomeOutpostModel>(),
            new List<UserOutpostEnhanceModel>(),
            UserInGameStatusModel.Empty,
            new List<MissionReceivedDailyBonusModel>(),
            new List<MissionEventDailyBonusRewardModel>(),
            new List<UserMissionEventDailyBonusProgressModel>(),
            new List<ComebackBonusRewardModel>(),
            new List<UserComebackBonusProgressModel>(),
            new List<UserEmblemModel>(),
            new List<UserArtworkModel>(),
            new List<UserArtworkFragmentModel>(),
            new List<UserGachaModel>(),
            new List<UserDrawCountThresholdModel>(),
            new List<UserReceivedUnitEncyclopediaRewardModel>(),
            new List<OprNoticeModel>(),
            UserStoreInfoModel.Empty,
            new List<UserItemTradeModel>(),
            new List<UserEnemyDiscoverModel>(),
            AdventBattleRaidTotalScoreModel.Empty,
            new List<UserShopPassModel>(),
            new List<UserTradePackModel>(),
            null,
            DateTimeOffset.MinValue,
            SysPvpSeasonModel.Empty,
            UserPvpStatusModel.Empty,
            new List<MngContentCloseModel>(),
            new List<UserExchangeLineupModel>()
        );

        public virtual bool Equals(GameFetchOtherModel other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other == null) return false;

            if (TutorialStatus != other.TutorialStatus) return false;
            if (UserProfileModel != other.UserProfileModel) return false;
            if (UserLoginInfoModel != other.UserLoginInfoModel) return false;
            if (UserIdleIncentiveModel != other.UserIdleIncentiveModel) return false;
            if (UserInGameStatusModel != other.UserInGameStatusModel) return false;
            if (UserStoreInfoModel != other.UserStoreInfoModel) return false;
            if (AdventBattleRaidTotalScoreModel != other.AdventBattleRaidTotalScoreModel) return false;

            if ((UserTutorialFreePartModels == null) ^ (other.UserTutorialFreePartModels == null)) return false;
            if (UserTutorialFreePartModels != null && other.UserTutorialFreePartModels != null)
            {
                if (!UserTutorialFreePartModels.SequenceEqual(other.UserTutorialFreePartModels)) return false;
            }

            if ((UserUnitModels == null) ^ (other.UserUnitModels == null)) return false;
            if (UserUnitModels != null && other.UserUnitModels != null)
            {
                if (!UserUnitModels.SequenceEqual(other.UserUnitModels)) return false;
            }

            if ((UserItemModels == null) ^ (other.UserItemModels == null)) return false;
            if (UserItemModels != null && other.UserItemModels != null)
            {
                if (!UserItemModels.SequenceEqual(other.UserItemModels)) return false;
            }

            if ((UserShopItemModels == null) ^ (other.UserShopItemModels == null)) return false;
            if (UserShopItemModels != null && other.UserShopItemModels != null)
            {
                if (!UserShopItemModels.SequenceEqual(other.UserShopItemModels)) return false;
            }

            if ((UserStoreProductModels == null) ^ (other.UserStoreProductModels == null)) return false;
            if (UserStoreProductModels != null && other.UserStoreProductModels != null)
            {
                if (!UserStoreProductModels.SequenceEqual(other.UserStoreProductModels)) return false;
            }

            if ((UserConditionPackModels == null) ^ (other.UserConditionPackModels == null)) return false;
            if (UserConditionPackModels != null && other.UserConditionPackModels != null)
            {
                if (!UserConditionPackModels.SequenceEqual(other.UserConditionPackModels)) return false;
            }

            if ((UserPartyModels == null) ^ (other.UserPartyModels == null)) return false;
            if (UserPartyModels != null && other.UserPartyModels != null)
            {
                if (!UserPartyModels.SequenceEqual(other.UserPartyModels)) return false;
            }

            if ((UserOutpostModels == null) ^ (other.UserOutpostModels == null)) return false;
            if (UserOutpostModels != null && other.UserOutpostModels != null)
            {
                if (!UserOutpostModels.SequenceEqual(other.UserOutpostModels)) return false;
            }

            if ((UserOutpostEnhanceModels == null) ^ (other.UserOutpostEnhanceModels == null)) return false;
            if (UserOutpostEnhanceModels != null && other.UserOutpostEnhanceModels != null)
            {
                if (!UserOutpostEnhanceModels.SequenceEqual(other.UserOutpostEnhanceModels)) return false;
            }

            if ((MissionReceivedDailyBonusModel == null) ^ (other.MissionReceivedDailyBonusModel == null)) return false;
            if (MissionReceivedDailyBonusModel != null && other.MissionReceivedDailyBonusModel != null)
            {
                if (!MissionReceivedDailyBonusModel.SequenceEqual(other.MissionReceivedDailyBonusModel)) return false;
            }

            if ((MissionEventDailyBonusRewardModels == null) ^ (other.MissionEventDailyBonusRewardModels == null)) return false;
            if (MissionEventDailyBonusRewardModels != null && other.MissionEventDailyBonusRewardModels != null)
            {
                if (!MissionEventDailyBonusRewardModels.SequenceEqual(other.MissionEventDailyBonusRewardModels)) return false;
            }

            if ((UserMissionEventDailyBonusProgressModels == null) ^ (other.UserMissionEventDailyBonusProgressModels == null))
            {
                return false;
            }
            if (UserMissionEventDailyBonusProgressModels != null && other.UserMissionEventDailyBonusProgressModels != null)
            {
                if (!UserMissionEventDailyBonusProgressModels.SequenceEqual(other.UserMissionEventDailyBonusProgressModels))
                {
                    return false;
                }
            }

            if ((ComebackBonusRewardModels == null) ^ (other.ComebackBonusRewardModels == null)) return false;
            if (ComebackBonusRewardModels != null && other.ComebackBonusRewardModels != null)
            {
                if (!ComebackBonusRewardModels.SequenceEqual(other.ComebackBonusRewardModels))
                {
                    return false;
                }
            }

            if ((UserComebackBonusProgressModels == null) ^ (other.UserComebackBonusProgressModels == null)) return false;
            if (UserComebackBonusProgressModels != null && other.UserComebackBonusProgressModels != null)
            {
                if (!UserComebackBonusProgressModels.SequenceEqual(other.UserComebackBonusProgressModels))
                {
                    return false;
                }
            }

            if ((UserEmblemModel == null) ^ (other.UserEmblemModel == null)) return false;
            if (UserEmblemModel != null && other.UserEmblemModel != null)
            {
                if (!UserEmblemModel.SequenceEqual(other.UserEmblemModel)) return false;
            }

            if ((UserArtworkModels == null) ^ (other.UserArtworkModels == null)) return false;
            if (UserArtworkModels != null && other.UserArtworkModels != null)
            {
                if (!UserArtworkModels.SequenceEqual(other.UserArtworkModels)) return false;
            }

            if ((UserArtworkFragmentModels == null) ^ (other.UserArtworkFragmentModels == null)) return false;
            if (UserArtworkFragmentModels != null && other.UserArtworkFragmentModels != null)
            {
                if (!UserArtworkFragmentModels.SequenceEqual(other.UserArtworkFragmentModels)) return false;
            }

            if ((UserGachaModels == null) ^ (other.UserGachaModels == null)) return false;
            if (UserGachaModels != null && other.UserGachaModels != null)
            {
                if (!UserGachaModels.SequenceEqual(other.UserGachaModels)) return false;
            }

            if ((UserGachaDrawCountThresholdModels == null) ^ (other.UserGachaDrawCountThresholdModels == null)) return false;
            if (UserGachaDrawCountThresholdModels != null && other.UserGachaDrawCountThresholdModels != null)
            {
                if (!UserGachaDrawCountThresholdModels.SequenceEqual(other.UserGachaDrawCountThresholdModels)) return false;
            }

            if ((UserReceivedUnitEncyclopediaRewardModels == null) ^ (other.UserReceivedUnitEncyclopediaRewardModels == null))
            {
                return false;
            }
            if (UserReceivedUnitEncyclopediaRewardModels != null && other.UserReceivedUnitEncyclopediaRewardModels != null)
            {
                if (!UserReceivedUnitEncyclopediaRewardModels.SequenceEqual(other.UserReceivedUnitEncyclopediaRewardModels))
                {
                    return false;
                }
            }

            if ((OprInGameNoticeModels == null) ^ (other.OprInGameNoticeModels == null)) return false;
            if (OprInGameNoticeModels != null && other.OprInGameNoticeModels != null)
            {
                if (!OprInGameNoticeModels.SequenceEqual(other.OprInGameNoticeModels)) return false;
            }

            if ((UserItemTradeModels == null) ^ (other.UserItemTradeModels == null)) return false;
            if (UserItemTradeModels != null && other.UserItemTradeModels != null)
            {
                if (!UserItemTradeModels.SequenceEqual(other.UserItemTradeModels)) return false;
            }

            if ((UserEnemyDiscoverModels == null) ^ (other.UserEnemyDiscoverModels == null)) return false;
            if (UserEnemyDiscoverModels != null && other.UserEnemyDiscoverModels != null)
            {
                if (!UserEnemyDiscoverModels.SequenceEqual(other.UserEnemyDiscoverModels)) return false;
            }

            if ((UserShopPassModels == null) ^ (other.UserShopPassModels == null)) return false;
            if (UserShopPassModels != null && other.UserShopPassModels != null)
            {
                if (!UserShopPassModels.SequenceEqual(other.UserShopPassModels)) return false;
            }

            if ((UserTradePackModels == null) ^ (other.UserTradePackModels == null)) return false;
            if (UserTradePackModels != null && other.UserTradePackModels != null)
            {
                if (!UserTradePackModels.SequenceEqual(other.UserTradePackModels)) return false;
            }

            if (SysPvpSeasonModel != null)
            {
                if (!SysPvpSeasonModel.Equals(other.SysPvpSeasonModel)) return false;
            }

            if (UserPvpStatusModel != null)
            {
                if (!UserPvpStatusModel.Equals(other.UserPvpStatusModel)) return false;
            }

            if ((MngContentCloseModels == null) ^ (other.MngContentCloseModels == null)) return false;
            if (MngContentCloseModels != null && other.MngContentCloseModels != null)
            {
                if (!MngContentCloseModels.SequenceEqual(other.MngContentCloseModels)) return false;
            }

            if ((UsrExchangeLineupModels == null) ^ (other.UsrExchangeLineupModels == null)) return false;
            if (UsrExchangeLineupModels != null && other.UsrExchangeLineupModels != null)
            {
                if (!UsrExchangeLineupModels.SequenceEqual(other.UsrExchangeLineupModels)) return false;
            }

            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();

            hash.Add(TutorialStatus);
            hash.Add(UserProfileModel);
            hash.Add(UserLoginInfoModel);
            hash.Add(UserIdleIncentiveModel);
            hash.Add(UserInGameStatusModel);
            hash.Add(UserStoreInfoModel);
            hash.Add(AdventBattleRaidTotalScoreModel);
            hash.Add(SysPvpSeasonModel);
            hash.Add(UserPvpStatusModel);

            AddHashCodes(hash, UserTutorialFreePartModels);
            AddHashCodes(hash, UserUnitModels);
            AddHashCodes(hash, UserItemModels);
            AddHashCodes(hash, UserShopItemModels);
            AddHashCodes(hash, UserStoreProductModels);
            AddHashCodes(hash, UserConditionPackModels);
            AddHashCodes(hash, UserPartyModels);
            AddHashCodes(hash, UserOutpostModels);
            AddHashCodes(hash, UserOutpostEnhanceModels);
            AddHashCodes(hash, MissionReceivedDailyBonusModel);
            AddHashCodes(hash, MissionEventDailyBonusRewardModels);
            AddHashCodes(hash, UserMissionEventDailyBonusProgressModels);
            AddHashCodes(hash, ComebackBonusRewardModels);
            AddHashCodes(hash, UserComebackBonusProgressModels);
            AddHashCodes(hash, UserEmblemModel);
            AddHashCodes(hash, UserArtworkModels);
            AddHashCodes(hash, UserArtworkFragmentModels);
            AddHashCodes(hash, UserGachaModels);
            AddHashCodes(hash, UserGachaDrawCountThresholdModels);
            AddHashCodes(hash, UserReceivedUnitEncyclopediaRewardModels);
            AddHashCodes(hash, OprInGameNoticeModels);
            AddHashCodes(hash, UserItemTradeModels);
            AddHashCodes(hash, UserEnemyDiscoverModels);
            AddHashCodes(hash, UserShopPassModels);
            AddHashCodes(hash, UserTradePackModels);
            AddHashCodes(hash, MngContentCloseModels);
            AddHashCodes(hash, UsrExchangeLineupModels);

            return hash.ToHashCode();
        }

        static void AddHashCodes<T>(HashCode hash, IReadOnlyList<T> models)
        {
            if (models == null) return;

            int start = models.Count > 10 ? models.Count - 10 : 0;
            for (int i = start; i < models.Count; i++)
            {
                hash.Add(models[i]);
            }
        }
    };
}
