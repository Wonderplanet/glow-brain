using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaRatio.Presentation.ViewModels
{
    public record GachaRatioDialogViewModel(
        GachaRatioPageViewModel NormalRatioPageViewModel,
        GachaRatioPageViewModel SSRRatioPageViewModel,
        GachaRatioPageViewModel URRatioPageViewModel,
        GachaRatioPageViewModel PickupRatioPageViewModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription);
}
