using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.UnitEnhance
{
    public record RankUpInfoModel(UnitLevel RequireLevel, UnitRank Rank)
    {
        public static RankUpInfoModel Empty { get; } = new(UnitLevel.Empty, UnitRank.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
