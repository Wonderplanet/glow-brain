using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record PreConversionResourceModel(
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount)
    {
        public static PreConversionResourceModel Empty { get; } = new PreConversionResourceModel(
            ResourceType.Coin, 
            MasterDataId.Empty, 
            ObscuredPlayerResourceAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
