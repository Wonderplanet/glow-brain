using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models
{
    public record MstAdventBattleScoreRankModel(
        MasterDataId Id,
        MasterDataId MstAdventBattleId,
        RankType RankType,
        AdventBattleScoreRankLevel ScoreRankLevel,
        AdventBattleScore RequiredLowerScore)
    {
        public static MstAdventBattleScoreRankModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleScore.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}