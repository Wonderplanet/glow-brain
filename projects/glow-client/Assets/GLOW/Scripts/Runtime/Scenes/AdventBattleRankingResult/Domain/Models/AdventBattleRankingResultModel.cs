using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Scenes.AdventBattleRankingResult.Domain.Models
{
    public record AdventBattleRankingResultModel(
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        AdventBattleRankingRank Rank,
        AdventBattleScore Score,
        IReadOnlyList<PlayerResourceModel> RewardList,
        AdventBattleType AdventBattleType,
        UnitImageAssetPath EnemyImageAssetPath,
        AdventBattleRankingExcludeRankingFlag IsExcludeRanking,
        AdventBattleName AdventBattleName)
    {
        public static AdventBattleRankingResultModel Empty { get; } = new(
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleRankingRank.Empty,
            AdventBattleScore.Empty,
            new List<PlayerResourceModel>(),
            AdventBattleType.ScoreChallenge,
            UnitImageAssetPath.Empty,
            AdventBattleRankingExcludeRankingFlag.False,
            AdventBattleName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
