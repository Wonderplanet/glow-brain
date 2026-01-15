using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioByRarityViewModel(
        GachaRatioRarityRatioItemViewModel UR,
        GachaRatioRarityRatioItemViewModel SSR,
        GachaRatioRarityRatioItemViewModel SR,
        GachaRatioRarityRatioItemViewModel R)
    {
        public static GachaRatioByRarityViewModel Empty { get; } = new GachaRatioByRarityViewModel(
            new GachaRatioRarityRatioItemViewModel(Rarity.UR, 0),
            new GachaRatioRarityRatioItemViewModel(Rarity.SSR, 0),
            new GachaRatioRarityRatioItemViewModel(Rarity.SR, 0),
            new GachaRatioRarityRatioItemViewModel(Rarity.R, 0)
        );
    };
}
