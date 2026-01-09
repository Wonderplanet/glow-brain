using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaResultModel(
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount,
        PreConversionResourceModel PreConversionResource);
}
