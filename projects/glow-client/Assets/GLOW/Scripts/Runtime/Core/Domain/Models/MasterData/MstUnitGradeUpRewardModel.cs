using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitGradeUpRewardModel(
        MasterDataId MstUnitId,
        UnitGrade GradeLevel,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount)
    {
        public static MstUnitGradeUpRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            UnitGrade.Empty,
            ResourceType.Artwork,   // 原画を渡す想定なのでデフォルトはArtwork
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
