using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels
{
    public record GachaLineupDialogViewModel(
        GachaLineupPageViewModel NormalRatioPageViewModel,
        GachaLineupPageViewModel SSRRatioPageViewModel,
        GachaLineupPageViewModel URRatioPageViewModel,
        GachaLineupPageViewModel PickupRatioPageViewModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription);
}