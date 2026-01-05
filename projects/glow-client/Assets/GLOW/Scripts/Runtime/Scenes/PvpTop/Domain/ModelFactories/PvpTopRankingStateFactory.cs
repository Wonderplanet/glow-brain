using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public class PvpTopRankingStateFactory : IPvpTopRankingStateFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }

        public PvpTopRankingState Create(
            MstPvpModel mstPvpModel,
            SysPvpSeasonModel sysPvpSeasonModel,
            ViewableRankingFromCalculatingFlag isViewableRankingFromCalculating)
        {
            var rankingTargetType = CreatePvpRankingTargetType(mstPvpModel.MinPvpRankClass);
            var openingType = CreatePvpRankingOpeningType(sysPvpSeasonModel, isViewableRankingFromCalculating);

            return new PvpTopRankingState(rankingTargetType, openingType);
        }

        PvpRankingTargetType CreatePvpRankingTargetType(PvpRankClassType? classType)
        {
            if (classType == null)
            {
                return PvpRankingTargetType.None;
            }

            return classType switch
            {
                PvpRankClassType.Bronze => PvpRankingTargetType.AllRank,
                PvpRankClassType.Silver => PvpRankingTargetType.SpecificRank,
                PvpRankClassType.Gold => PvpRankingTargetType.SpecificRank,
                PvpRankClassType.Platinum => PvpRankingTargetType.SpecificRank,
                _ => PvpRankingTargetType.None
            };
        }

        PvpRankingOpeningType CreatePvpRankingOpeningType(
            SysPvpSeasonModel sysPvpSeasonModel,
            ViewableRankingFromCalculatingFlag isViewableRankingFromCalculating)
        {
            // 集計中だとapi経由で貰ったらCalculating
            if (!isViewableRankingFromCalculating)
            {
                return PvpRankingOpeningType.Calculating;
            }

            // NowがClosedAtより後ろならNotStarted
            if (!CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    sysPvpSeasonModel.StartAt.Value,
                    sysPvpSeasonModel.EndAt.Value
                ))
            {
                return PvpRankingOpeningType.NotStarted;
            }

            //集計中ステータスあるが使ってない
            return PvpRankingOpeningType.Opening;

        }
    }
}
