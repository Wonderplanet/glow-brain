using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaRatio.Domain.Model
{
    public record GachaRatioRarityRatioModel(
        GachaRatioRarityRatioItemModel UR,
        GachaRatioRarityRatioItemModel SSR,
        GachaRatioRarityRatioItemModel SR,
        GachaRatioRarityRatioItemModel R)
    {
        public static GachaRatioRarityRatioModel Empty { get; } = new(
            new GachaRatioRarityRatioItemModel(Rarity.UR, 0f),
            new GachaRatioRarityRatioItemModel(Rarity.SSR, 0f),
            new GachaRatioRarityRatioItemModel(Rarity.SR, 0f),
            new GachaRatioRarityRatioItemModel(Rarity.R, 0f)
        );
    };
}
