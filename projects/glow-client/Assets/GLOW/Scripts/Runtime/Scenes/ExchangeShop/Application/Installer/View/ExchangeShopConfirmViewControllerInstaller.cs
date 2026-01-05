using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Services;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.ExchangeShop.Presentation.WireFrame;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Application.Installer.View
{
    public class ExchangeShopConfirmViewControllerInstaller : Zenject.Installer
    {
        [Inject] ExchangeShopConfirmViewController.Argument argument;
        public override void InstallBindings()
        {
            Container.BindInstance(argument).AsCached();
            Container.BindViewWithKernal<ExchangeShopConfirmViewController>();

            Container.BindInterfacesTo<ExchangeConfirmPresenter>().AsCached();
            Container.Bind<ExchangeConfirmWireFrame>().AsCached();

            Container.Bind<ExchangeApi>().AsCached();
            Container.BindInterfacesTo<ExchangeService>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentAcquisitionModelFactory>().AsCached();
            Container.Bind<ApplyExchangeTradeUseCase>().AsCached();
            Container.Bind<GetExchangeableUseCase>().AsCached();
        }
    }
}
