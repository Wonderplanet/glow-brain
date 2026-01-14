using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;

namespace GLOW.Core.Domain.Models
{
    public record MstBoxGachaPrizeModel(
        MasterDataId Id,
        BoxGachaGroupId GroupId,
        PickUpFlag IsPickUp,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        BoxGachaPrizeStock Stock)
    {
        public static MstBoxGachaPrizeModel Empty { get; } = new(
            MasterDataId.Empty,
            BoxGachaGroupId.Empty,
            PickUpFlag.False,
            ResourceType.Coin,
            MasterDataId.Empty,
            ObscuredPlayerResourceAmount.Empty,
            BoxGachaPrizeStock.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}