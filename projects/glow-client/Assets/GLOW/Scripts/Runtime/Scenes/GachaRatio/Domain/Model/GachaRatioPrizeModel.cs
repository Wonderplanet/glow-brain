using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioPrizeModel(ResourceType ResourceType, MasterDataId MasterDataId, PlayerResourceAmount Amount, PickupFlag PickupFlag, OutputRatio OutputRatio)
    {
        public static GachaRatioPrizeModel Empty { get; } = new(ResourceType.FreeDiamond, MasterDataId.Empty, PlayerResourceAmount.Empty, PickupFlag.Empty, OutputRatio.Zero);
    }
}
