using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleRankingItemModel(
        UserMyId UserMyId,
        AdventBattleRankingRank Rank,
        UserName UserName,
        MasterDataId MstUnitId,
        MasterDataId MstEmblemId,
        AdventBattleScore MaxScore,
        AdventBattleScore TotalScore)
    {
        public static AdventBattleRankingItemModel Empty { get; } = new(
            UserMyId.Empty,
            AdventBattleRankingRank.Empty,
            UserName.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty);
    }
}
