using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaAnim.Domain.Model
{
    public record GachaAnimResultModel(
        ResourceType ResourceType,
        GashaAnimProduction GashaAnimProduction,
        GachaAnimUnitModel UnitModel,
        GachaAnimItemModel ItemModel,
        IsNewUnitBadge IsNewUnitBadge
    )
    {
        public static GachaAnimResultModel Empty { get; } = new(
            ResourceType.FreeDiamond,
            GashaAnimProduction.Empty,
            GachaAnimUnitModel.Empty,
            GachaAnimItemModel.Empty,
            IsNewUnitBadge.Empty
            );
    }
}
