using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.TradeShop.Presentation.View;
using UIKit.ZenjectBridge;

namespace GLOW.Scenes.ExchangeShop.Application.Installer.View
{
    public class FragmentTradeShopTopViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<FragmentTradeShopTopViewController>();
            Container.BindInterfacesTo<FragmentTradeShopTopPresenter>().AsCached();

            Container.Bind<GetTradeFragmentUseCase>().AsCached();
            Container.Bind<GetItemBoxItemUseCase>().AsCached();
        }
    }
}
