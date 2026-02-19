using GLOW.Core.Domain.Models.UnitEnhance;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitSpecificRankUpModel(
        MasterDataId MstUnitId,
        UnitRank Rank,
        ItemAmount ColorMemoryAmount,
        ItemAmount UnitMemoryAmount,
        UnitLevel RequireLevel,
        ItemAmount SrMemoryFragmentAmount,
        ItemAmount SsrMemoryFragmentAmount,
        ItemAmount UrMemoryFragmentAmount
    )
    {
        public static MstUnitSpecificRankUpModel Empty { get; } = new (
            MasterDataId.Empty,
            UnitRank.Empty,
            ItemAmount.Empty,
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
