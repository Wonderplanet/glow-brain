using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.UnitEnhance;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitRankUpModel(
        UnitRank Rank,
        UnitLabel UnitLabel,
        ItemAmount ColorMemoryAmount,
        UnitLevel RequireLevel,
        ItemAmount SrMemoryFragmentAmount,
        ItemAmount SsrMemoryFragmentAmount,
        ItemAmount UrMemoryFragmentAmount)
    {
        public static MstUnitRankUpModel Empty { get; } = new(
            UnitRank.Empty,
            UnitLabel.DropR,
            ItemAmount.Empty,
            UnitLevel.Empty,
            ItemAmount.Empty,
            ItemAmount.Empty,
            ItemAmount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public RankUpInfoModel ToRankUpInfoModel()
        {
            return IsEmpty()
                ? RankUpInfoModel.Empty
                : new RankUpInfoModel(RequireLevel, Rank);
        }
    }
}
