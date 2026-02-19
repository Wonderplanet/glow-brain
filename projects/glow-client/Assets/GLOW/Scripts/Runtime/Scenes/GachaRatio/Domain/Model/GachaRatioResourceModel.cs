using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioResourceModel(ResourceType ResourceType, MasterDataId MasterDataId, PlayerResourceAmount Amount)
    {
        public static GachaRatioResourceModel Empty { get; } = new GachaRatioResourceModel(ResourceType.Unit, MasterDataId.Empty, PlayerResourceAmount.Empty);
    };
}
