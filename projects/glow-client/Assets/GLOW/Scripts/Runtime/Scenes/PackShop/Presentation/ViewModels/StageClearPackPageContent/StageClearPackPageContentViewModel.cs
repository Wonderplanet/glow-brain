using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShop.Presentation.ViewModels.StageClearPackPageContent
{
    public record StageClearPackPageContentViewModel(PackShopProductViewModel ViewModel,
        Action<PackShopProductViewModel> BuyEvent,
        Action<MasterDataId> InfoEvent);
}
