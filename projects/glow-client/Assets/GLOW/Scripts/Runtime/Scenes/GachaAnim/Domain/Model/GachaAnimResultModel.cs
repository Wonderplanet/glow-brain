using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaAnim.Domain.Model
{
    public record GachaAnimResultModel(
        ResourceType ResourceType,
        GachaAnimUnitModel UnitModel,
        GachaAnimItemModel ItemModel,
        IsNewUnitBadge IsNewUnitBadge,
        // 昇格演出用の表示レアリティのため、実際より低いことがある
        Rarity DisplayRarity
    )
    {
        public static GachaAnimResultModel Empty { get; } = new(
            ResourceType.FreeDiamond,
            GachaAnimUnitModel.Empty,
            GachaAnimItemModel.Empty,
            IsNewUnitBadge.Empty,
            Rarity.R
            );
    }
}
