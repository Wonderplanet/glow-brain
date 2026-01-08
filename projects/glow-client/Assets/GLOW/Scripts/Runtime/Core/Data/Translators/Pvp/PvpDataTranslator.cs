using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators.Pvp
{
    public class PvpDataTranslator
    {
        public static PvpTopResultModel ToPvpTopResultModel(PvpTopResultData data)
        {
            if (data == null)
            {
                return PvpTopResultModel.Empty;
            }

            var pvpPreviousSeasonResult = data.PvpPreviousSeasonResult != null
                ? ToPvpPreviousSeasonResultModel(data.PvpPreviousSeasonResult)
                : PvpPreviousSeasonResultModel.Empty;

            var opponentSelectStatuses = data.OpponentSelectStatuses == null
                ? new List<OpponentSelectStatusModel>()
                : data.OpponentSelectStatuses.Select(ToOpponentSelectStatusModel).ToList();

            return new PvpTopResultModel(
                ToPvpHeldStatusModel(data.PvpHeldStatus),
                ToUserPvpStatusModel(data.UsrPvpStatus),
                opponentSelectStatuses,
                pvpPreviousSeasonResult,
                new ViewableRankingFromCalculatingFlag(data.IsViewableRanking)
            );
        }

        public static PvpStartResultModel ToPvpStartResultModel(PvpStartResultData data)
        {
            if (data == null)
            {
                return PvpStartResultModel.Empty;
            }

            return new PvpStartResultModel(ToOpponentPvpStatusModel(data.OpponentPvpStatus));
        }

        public static PvpEndResultModel ToPvpEndResultModel(PvpEndResultData data)
        {
            if (data == null)
            {
                return PvpEndResultModel.Empty;
            }

            var usrItems = data.UsrItems == null
                ? new List<UserItemModel>()
                : data.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();

            var pvpRewards = data.PvpTotalScoreRewards == null
                ? new List<PvpRewardModel>()
                : data.PvpTotalScoreRewards.Select(ToPvpRewardModel).ToList();

            var usrEmblems = data.UsrEmblems == null
                ? new List<UserEmblemModel>()
                : data.UsrEmblems.Select(UserEmblemDataTranslator.ToUserEmblemModel).ToList();

            return new PvpEndResultModel(
                ToUserPvpStatusModel(data.UsrPvpStatus),
                ToPvpEndResultBonusPointModel(data.PvpEndResultBonusPoint),
                pvpRewards,
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                usrItems,
                usrEmblems
            );
        }

        public static PvpResumeResultModel ToPvpResumeResultModel(PvpResumeResultData data)
        {
            if (data == null)
            {
                return PvpResumeResultModel.Empty;
            }

            return new PvpResumeResultModel(
                ToOpponentSelectStatusModel(data.OpponentSelectStatus),
                ToOpponentPvpStatusModel(data.OpponentPvpStatus)
            );
        }

        public static PvpChangeOpponentResultModel ToPvpChangeOpponentResultModel(PvpChangeOpponentResultData data)
        {
            if (data == null)
            {
                return PvpChangeOpponentResultModel.Empty;
            }

            var opponentStatuses = data.OpponentSelectStatuses == null
                ? new List<OpponentSelectStatusModel>()
                : data.OpponentSelectStatuses.Select(ToOpponentSelectStatusModel).ToList();

            return new PvpChangeOpponentResultModel(
                opponentStatuses);
        }

        public static PvpRankingResultModel ToPvpRankingResultModel(PvpRankingResultData data)
        {
            if (data == null)
            {
                return PvpRankingResultModel.Empty;
            }

            var ranking = data.Ranking == null
                ? new List<PvpOtherUserRankingModel>()
                : data.Ranking.Select(ToPvpOtherUserRankingModel).ToList();

            return new PvpRankingResultModel(
                ranking,
                ToPvpMyRankingModel(data.MyRanking)
            );
        }

        public static SysPvpSeasonModel ToSysPvpSeasonModel(SysPvpSeasonData data)
        {
            if (data == null)
            {
                return SysPvpSeasonModel.Empty;
            }

            return new SysPvpSeasonModel(
                new ContentSeasonSystemId(data.Id),
                new PvpStartAt(data.StartAt),
                new PvpEndAt(data.EndAt),
                data.ClosedAt.HasValue
                    ? new PvpClosedAt(data.ClosedAt.Value)
                    : PvpClosedAt.Empty
            );
        }

        public static UserPvpStatusModel ToUserPvpStatusModel(UsrPvpStatusData data)
        {
            if (data == null)
            {
                return UserPvpStatusModel.Empty;
            }

            return new UserPvpStatusModel(
                new PvpPoint(data.Score),
                new PvpPoint(data.MaxReceivedScoreReward),
                data.PvpRankClassType,
                new PvpRankLevel(data.PvpRankClassLevel),
                new PvpDailyChallengeCount(data.DailyRemainingChallengeCount),
                new PvpDailyChallengeCount(data.DailyRemainingItemChallengeCount)
            );
        }

        public static PvpAbortResultModel ToPvpAbortResultModel(PvpAbortResultData data)
        {
            if (data == null)
            {
                return PvpAbortResultModel.Empty;
            }

            var usrItems = data.UsrItems == null
                ? new List<UserItemModel>()
                : data.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            return new PvpAbortResultModel(
                ToUserPvpStatusModel(data.UsrPvpStatus),
                usrItems
                );
        }

        static OpponentPvpStatusModel ToOpponentPvpStatusModel(OpponentPvpStatusData data)
        {
            if (data == null)
            {
                return OpponentPvpStatusModel.Empty;
            }

            var pvpUnits = data.PvpUnits == null
                ? new List<PvpUnitModel>()
                : data.PvpUnits.Select(ToPvpUnitModel).ToList();
            var usrOutpostEnhancements = data.UsrOutpostEnhancements == null
                ? new List<UserOutpostEnhanceModel>()
                : data.UsrOutpostEnhancements.Select(ToUsrOutpostEnhancementModel).ToList();
            var usrEncyclopediaEffects = data.UsrEncyclopediaEffects == null
                ? new List<PvpEncyclopediaEffectModel>()
                : data.UsrEncyclopediaEffects.Select(ToPvpEncyclopediaEffectModel).ToList();
            var mstArtworkIds = data.MstArtworkIds == null
                ? new List<MasterDataId>()
                : data.MstArtworkIds.Select(x => string.IsNullOrEmpty(x) ? MasterDataId.Empty : new MasterDataId(x)).ToList();
            return new OpponentPvpStatusModel(
                pvpUnits,
                usrOutpostEnhancements,
                usrEncyclopediaEffects,
                mstArtworkIds
            );
        }

        static PvpUnitModel ToPvpUnitModel(PvpUnitData data)
        {
            if (data == null)
            {
                return PvpUnitModel.Empty;
            }

            return new PvpUnitModel(
                string.IsNullOrEmpty(data.MstUnitId) ?
                    MasterDataId.Empty :
                    new MasterDataId(data.MstUnitId),
                new UnitLevel(data.Level),
                new UnitRank(data.Rank),
                new UnitGrade(data.GradeLevel)
            );
        }

        static UserOutpostEnhanceModel ToUsrOutpostEnhancementModel(UsrOutpostEnhancementData data)
        {
            if (data == null)
            {
                return UserOutpostEnhanceModel.Empty;
            }

            return new UserOutpostEnhanceModel(
                string.IsNullOrEmpty(data.MstOutpostId) ?
                    MasterDataId.Empty :
                    new MasterDataId(data.MstOutpostId),
                string.IsNullOrEmpty(data.MstOutpostEnhancementId) ?
                    MasterDataId.Empty :
                    new MasterDataId(data.MstOutpostEnhancementId),
                new OutpostEnhanceLevel(data.Level)
            );
        }

        static PvpEncyclopediaEffectModel ToPvpEncyclopediaEffectModel(PvpEncyclopediaEffectData data)
        {
            if (data == null)
            {
                return PvpEncyclopediaEffectModel.Empty;
            }

            return new PvpEncyclopediaEffectModel(
                string.IsNullOrEmpty(data.MstEncyclopediaEffectId) ?
                    MasterDataId.Empty :
                    new MasterDataId(data.MstEncyclopediaEffectId)
            );
        }

        static OpponentSelectStatusModel ToOpponentSelectStatusModel(OpponentSelectStatusData data)
        {
            if (data == null)
            {
                return OpponentSelectStatusModel.Empty;
            }

            return new OpponentSelectStatusModel(
                new UserMyId(data.MyId),
                new UserName(data.Name),
                string.IsNullOrEmpty(data.MstUnitId) ? MasterDataId.Empty : new MasterDataId(data.MstUnitId),
                string.IsNullOrEmpty(data.MstEmblemId) ? MasterDataId.Empty : new MasterDataId(data.MstEmblemId),
                new PvpPoint(data.Score),
                ToOpponentPvpStatusModel(data.OpponentPvpStatus),
                new PvpPoint(data.WinAddPoint)
            );
        }

        static PvpHeldStatusModel ToPvpHeldStatusModel(PvpHeldStatusData data)
        {
            if (data == null)
            {
                return PvpHeldStatusModel.Empty;
            }

            return new PvpHeldStatusModel(
                new ContentSeasonSystemId(data.SysPvpSeasonId),
                new PvpHeldNumber(data.HeldNumber),
                new PvpStartAt(data.StartAt),
                new PvpEndAt(data.EndAt)
            );
        }

        static PvpPreviousSeasonResultModel ToPvpPreviousSeasonResultModel(PvpPreviousSeasonResultData data)
        {
            if (data == null)
            {
                return PvpPreviousSeasonResultModel.Empty;
            }

            var pvpRewards = data.PvpRewards == null
                ? new List<PvpRewardModel>()
                : data.PvpRewards.Select(ToPvpRewardModel).ToList();
            return new PvpPreviousSeasonResultModel(
                data.PvpRankClassType,
                new PvpRankLevel(data.RankClassLevel),
                new PvpPoint(data.Score),
                data.Ranking == 0 ? PvpRankingRank.Empty : new PvpRankingRank(data.Ranking),
                pvpRewards
            );
        }

        static PvpRewardModel ToPvpRewardModel(PvpRewardData data)
        {
            if (data == null)
            {
                return PvpRewardModel.Empty;
            }

            return new PvpRewardModel(
                data.RewardCategory,
                RewardDataTranslator.Translate(data.Reward)
            );
        }

        static PvpOtherUserRankingModel ToPvpOtherUserRankingModel(PvpRankingItemData data)
        {
            if (data == null)
            {
                return PvpOtherUserRankingModel.Empty;
            }

            return new PvpOtherUserRankingModel(
                string.IsNullOrEmpty(data.MyId) ? UserMyId.Empty : new UserMyId(data.MyId),
                data.Rank == 0 ? PvpRankingRank.Empty : new PvpRankingRank(data.Rank),
                new PvpPoint(data.Score),
                new UserName(data.Name),
                string.IsNullOrEmpty(data.MstUnitId) ? MasterDataId.Empty : new MasterDataId(data.MstUnitId),
                string.IsNullOrEmpty(data.MstEmblemId) ? MasterDataId.Empty : new MasterDataId(data.MstEmblemId)
            );
        }

        static PvpMyRankingModel ToPvpMyRankingModel(PvpMyRankingData data)
        {
            if (data == null)
            {
                return PvpMyRankingModel.Empty;
            }

            return new PvpMyRankingModel(
                data.Rank == 0 ? PvpRankingRank.Empty : new PvpRankingRank(data.Rank),
                new PvpPoint(data.Score),
                new PvpExcludeRankingFlag(data.IsExcludeRanking)
            );
        }

        static PvpEndResultBonusPointModel ToPvpEndResultBonusPointModel(PvpEndResultBonusPointData data)
        {
            if (data == null)
            {
                return PvpEndResultBonusPointModel.Empty;
            }

            return new PvpEndResultBonusPointModel(
                new PvpPoint(data.ResultPoint),
                new PvpPoint(data.OpponentBonusPoint),
                new PvpPoint(data.TimeBonusPoint)
            );
        }
    }
}
