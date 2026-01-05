using System.Collections.Generic;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaListUseCaseModel(
        List<FestivalGachaBannerModel> FestivalBannerModels,
        List<GachaBannerModel> PickupBannerModels,
        List<GachaBannerModel> FreeBannerModels,
        List<GachaBannerModel> TicketBannerModels,
        List<GachaBannerModel> PaidOnlyBannerModels,
        GachaBannerModel TutorialBannerModel,
        List<MedalGachaModel> MedalBannerModels,
        PremiumGachaModel PremiumGachaModel,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel
    );
}
