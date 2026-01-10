using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.PackShop.Presentation.ViewModels;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public interface IPackShopViewDelegate
    {
        void OnViewWillAppear();
        TimeSpan GetRemainCountDown(EndDateTime endTime);
        void OnBuyProductSelected(PackShopProductViewModel packViewModel);
        void OnShowInfoSelected(MasterDataId oprProductId);
    }
}
