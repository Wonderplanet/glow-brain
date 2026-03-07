using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstDailyBonusRewardModel(
        MasterDataId Id,
        MasterDataId GroupId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount)
    {
        public static MstDailyBonusRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}