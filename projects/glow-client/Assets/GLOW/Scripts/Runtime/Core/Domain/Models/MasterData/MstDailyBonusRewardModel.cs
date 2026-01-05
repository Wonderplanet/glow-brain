using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstDailyBonusRewardModel(
        MasterDataId Id,
        MasterDataId GroupId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        SortOrder SortOrder)
    {
        public static MstDailyBonusRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty,
            SortOrder.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}