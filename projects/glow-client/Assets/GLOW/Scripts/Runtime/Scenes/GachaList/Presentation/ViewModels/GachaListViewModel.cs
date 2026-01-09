using System.Collections.Generic;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels
{
    public record GachaListViewModel(
        IReadOnlyList<FestivalGachaBannerViewModel> FestivalBannerViewModels,
        IReadOnlyList<GachaBannerViewModel> PickupBannerViewModels,
        IReadOnlyList<GachaBannerViewModel> FreeBannerViewModels,
        IReadOnlyList<GachaBannerViewModel> TicketBannerViewModels,
        IReadOnlyList<GachaBannerViewModel> PaidOnlyBannerViewModels,
        IReadOnlyList<MedalGachaBannerViewModel> MedalGachaBannerViewModels,
        PremiumGachaViewModel PremiumGachaViewModel,
        TutorialGachaBannerViewModel TutorialGachaBannerViewModel,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel);
}
