using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRanking.Domain.ValueObjects;
namespace GLOW.Scenes.AdventBattleRanking.Domain.Models
{
    public record AdventBattleRankingOtherUserUseCaseModel(
        UserMyId UserMyId,
        UserName UserName,
        AdventBattleScore MaxScore,
        EmblemAssetKey EmblemAssetKey,
        UnitAssetKey UnitAssetKey,
        AdventBattleRankingRank Rank,
        AdventBattleRankingMyselfFlag IsMyself,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel)
    {
        public static AdventBattleRankingOtherUserUseCaseModel Empty { get; } = new (
            UserMyId.Empty,
            UserName.Empty,
            AdventBattleScore.Empty,
            EmblemAssetKey.Empty,
            UnitAssetKey.Empty,
            AdventBattleRankingRank.Empty,
            AdventBattleRankingMyselfFlag.False,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
