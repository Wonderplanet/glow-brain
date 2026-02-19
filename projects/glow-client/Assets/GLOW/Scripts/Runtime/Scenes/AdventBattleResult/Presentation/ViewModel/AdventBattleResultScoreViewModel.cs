using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using ModestTree;

namespace GLOW.Scenes.AdventBattleResult.Presentation.ViewModel
{
    public record AdventBattleResultScoreViewModel(
        RankType CurrentRankType,
        AdventBattleScoreRankLevel CurrentScoreRankLevel,
        IReadOnlyList<AdventBattleResultScoreRankTargetViewModel> AdventBattleResultScoreRankTargetModels,
        IReadOnlyList<PlayerResourceIconViewModel> RankRewards,
        AdventBattleScore DamageScore,
        AdventBattleScore EnemyDefeatScore,
        AdventBattleScore BossEnemyDefeatScore)
    {
        public (RankType rankType, AdventBattleScoreRankLevel rankLevel) LastAchievedRankAndLevel()
        {
            var lastAchievedTarget = AdventBattleResultScoreRankTargetModels
                .Where(x => x.IsAchievedRank())
                .LastOrDefault(AdventBattleResultScoreRankTargetViewModel.Empty);
            if (lastAchievedTarget.IsEmpty())
            {
                return (CurrentRankType, CurrentScoreRankLevel);
            }
            
            return (lastAchievedTarget.TargetRankType, lastAchievedTarget.TargetScoreRankLevel);
        }

        public bool IsRankOrRankLevelUp()
        {
            // 空の場合はの場合はそもそもランクアップしない
            if (AdventBattleResultScoreRankTargetModels.IsEmpty()) return false;

            var lastAchievedRankAndLevel = AdventBattleResultScoreRankTargetModels
                .Where(x => x.IsAchievedRank())
                .LastOrDefault(AdventBattleResultScoreRankTargetViewModel.Empty);
            
            if (lastAchievedRankAndLevel.IsEmpty())
            {
                return false;
            }

            if (lastAchievedRankAndLevel.TargetRankType > CurrentRankType)
            {
                return true;
            }
            else if (lastAchievedRankAndLevel.TargetRankType == CurrentRankType &&
                     lastAchievedRankAndLevel.TargetScoreRankLevel > CurrentScoreRankLevel)
            {
                return true;
            }

            return false;
        }

        public bool IsScoreUpdated()
        {
            if (!AdventBattleResultScoreRankTargetModels.Any())
            {
                return false;
            }

            return AdventBattleResultScoreRankTargetModels.Count > 1 ||
                   AdventBattleResultScoreRankTargetModels[0].IsScoreUpdated();
        }
    }
}
