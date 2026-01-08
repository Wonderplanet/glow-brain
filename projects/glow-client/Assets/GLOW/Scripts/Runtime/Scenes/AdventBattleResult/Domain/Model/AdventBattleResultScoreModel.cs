using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.AdventBattleResult.Domain.Model
{
    public record AdventBattleResultScoreModel(
        RankType CurrentRankType,
        AdventBattleScoreRankLevel CurrentScoreRankLevel,
        IReadOnlyList<AdventBattleResultScoreRankTargetModel> AdventBattleResultScoreRankTargetModels,
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        AdventBattleScore DamageScore,
        AdventBattleScore EnemyDefeatScore,
        AdventBattleScore BossEnemyDefeatScore)
    {
        public static AdventBattleResultScoreModel Empty { get; } = new AdventBattleResultScoreModel(
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            new List<AdventBattleResultScoreRankTargetModel>(),
            new List<CommonReceiveResourceModel>(),
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
