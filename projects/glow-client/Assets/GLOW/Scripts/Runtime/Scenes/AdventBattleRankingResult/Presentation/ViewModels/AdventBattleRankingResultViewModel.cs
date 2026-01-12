using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;
namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels
{
    public record AdventBattleRankingResultViewModel(
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        AdventBattleRankingRank Rank,
        AdventBattleScore Score,
        IReadOnlyList<PlayerResourceIconViewModel> RewardList,
        AdventBattleType AdventBattleType,
        UnitImageAssetPath EnemyImageAssetPath,
        AdventBattleName AdventBattleName)
    {
        public static AdventBattleRankingResultViewModel Empty { get; } = new(
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleRankingRank.Empty,
            AdventBattleScore.Empty,
            new List<PlayerResourceIconViewModel>(),
            AdventBattleType.ScoreChallenge,
            UnitImageAssetPath.Empty,
            AdventBattleName.Empty);
    }
}
