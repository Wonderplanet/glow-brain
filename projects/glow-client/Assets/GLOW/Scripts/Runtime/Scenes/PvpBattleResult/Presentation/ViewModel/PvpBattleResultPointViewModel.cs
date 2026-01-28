using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.ViewModel
{
    public record PvpBattleResultPointViewModel(
        PvpRankClassType CurrentRankType,
        PvpRankLevel CurrentRankLevel,
        IReadOnlyList<PvpBattleResultPointRankTargetViewModel> PvpResultPointRankTargetModels,
        PvpPoint VictoryPoint,
        PvpPoint OpponentBonusPoint,
        PvpPoint TimeBonusPoint,
        PvpPoint TotalPoint)
    {
        public static PvpBattleResultPointViewModel Empty { get; } = new PvpBattleResultPointViewModel(
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            new List<PvpBattleResultPointRankTargetViewModel>(),
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty);

        public PvpPoint GainedTotalPoint => VictoryPoint + OpponentBonusPoint + TimeBonusPoint;

        public PvpBattleResultRankAnimationGaugeRate FillRate()
        {

            if (PvpResultPointRankTargetModels.IsEmpty() && CurrentRankLevel.IsZero())
            {
                return PvpBattleResultRankAnimationGaugeRate.Zero;
            }
            else
            {
                return PvpBattleResultRankAnimationGaugeRate.One;
            }
        }

        public (PvpRankClassType rankType, PvpRankLevel rankLevel) LastAchievedRankAndLevel()
        {
            // 最後に達成したランクとレベルを取得
            PvpBattleResultPointRankTargetViewModel lastAchievedTarget = PvpBattleResultPointRankTargetViewModel.Empty;
            lastAchievedTarget = IsRankOrRankLevelUp() ? GetRankUpTargetModel() : GetRankDownTargetModel();

            if (lastAchievedTarget.IsEmpty())
            {
                return (CurrentRankType, CurrentRankLevel);
            }

            return (lastAchievedTarget.TargetRankType, lastAchievedTarget.TargetScoreRankLevel);
        }


        public bool IsRankOrRankLevelUp()
        {
            // 空の場合はの場合はそもそもランクアップしない
            if (PvpResultPointRankTargetModels.IsEmpty()) return false;

            var lastAchievedTarget = PvpResultPointRankTargetModels
                .Where(x => x.IsAchievedRank())
                .LastOrDefault(PvpBattleResultPointRankTargetViewModel.Empty);
            
            if (lastAchievedTarget.IsEmpty())
            {
                return false;
            }

            if (lastAchievedTarget.TargetRankType > CurrentRankType)
            {
                return true;
            }
            else if (lastAchievedTarget.TargetRankType == CurrentRankType &&
                     lastAchievedTarget.TargetScoreRankLevel > CurrentRankLevel)
            {
                return true;
            }

            return false;
        }
        
        public bool IsCurrentRankMaxLevel()
        {
            return CurrentRankType == PvpConst.PvpMaxRankClassType && CurrentRankLevel.IsMaxLevel();
        }

        public bool IsKeepRankMaxLevel()
        {
            return IsCurrentRankMaxLevel() && GetRankDownTargetModel().IsEmpty();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        PvpBattleResultPointRankTargetViewModel GetRankUpTargetModel()
        {
            return PvpResultPointRankTargetModels
                .Where(model => model.IsAchievedRank())
                .LastOrDefault(PvpBattleResultPointRankTargetViewModel.Empty);
        }

        PvpBattleResultPointRankTargetViewModel GetRankDownTargetModel()
        {
            // ランクダウン時は末尾から１つ前の要素を取得
            var index = PvpResultPointRankTargetModels.Count - 2;
            if (index < 0)
            {
                var nextRankModel = PvpResultPointRankTargetModels
                    .FirstOrDefault(PvpBattleResultPointRankTargetViewModel.Empty);

                if (PvpConst.MinRankAndLevel == (nextRankModel.TargetRankType, nextRankModel.TargetScoreRankLevel))
                {
                    return nextRankModel;
                }

                return PvpBattleResultPointRankTargetViewModel.Empty;
            }

            return PvpResultPointRankTargetModels[index];
        }
    }
}
