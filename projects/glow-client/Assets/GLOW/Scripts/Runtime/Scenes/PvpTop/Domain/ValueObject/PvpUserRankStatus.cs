using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpUserRankStatus(PvpRankClassType PvpRankClassType, PvpTier PvpTier)
    {
        public static PvpUserRankStatus Empty { get; } = new(PvpRankClassType.Bronze, PvpTier.Zero);

        public bool IsEmpty() => this.Equals(Empty);

        public ScoreRankLevel ToScoreRankLevel()
        {
            return new ScoreRankLevel(PvpTier.Value);
        }

        public RankType ToRankType()
        {
            return PvpRankClassType switch
            {
                PvpRankClassType.Bronze => RankType.Bronze,
                PvpRankClassType.Silver => RankType.Silver,
                PvpRankClassType.Gold => RankType.Gold,
                PvpRankClassType.Platinum => RankType.Master,
                _ => RankType.Bronze
            };
        }
    };
}
