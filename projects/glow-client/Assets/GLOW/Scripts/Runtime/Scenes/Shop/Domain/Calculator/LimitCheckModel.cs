using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Shop.Domain.Calculator
{
    public record LimitCheckModel(MasterDataId MstId, ResourceType ResourceType, int Amount);
}
