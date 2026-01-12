using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaAnim.Domain.Model
{
    public record GachaAnimItemModel(
        MasterDataId Id,
        ItemName Name,
        Rarity Rarity,
        PlayerResourceAmount ResourceAmount,
        ItemAssetKey ItemAssetKey
    )
    {
        public static GachaAnimItemModel Empty { get; } = new(
            MasterDataId.Empty,
            ItemName.Empty,
            Rarity.R,
            PlayerResourceAmount.Empty,
            ItemAssetKey.Empty
        );
    }
}
