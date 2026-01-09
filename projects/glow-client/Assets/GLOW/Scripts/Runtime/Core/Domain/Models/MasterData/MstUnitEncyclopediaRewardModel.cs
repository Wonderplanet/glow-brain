using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitEncyclopediaRewardModel(
        MasterDataId Id,
        UnitGrade UnitEncyclopediaRank,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount
    )
    {
        public static MstUnitEncyclopediaRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            UnitGrade.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty
        );
    }
}
