using GLOW.Core.Data.Services;
using GLOW.Scenes.FragmentProvisionRatio.Application;
using GLOW.Scenes.FragmentProvisionRatio.Presentation;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Application.Installers.View;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Application.Views
{
    public class RandomFragmentBoxViewControllerInstaller : Installer
    {
        [Inject] RandomFragmentBoxViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindInterfacesTo<ViewFactory>().AsCached();

            Container.BindInterfacesTo<ItemService>().AsCached();
            Container.Bind<ConsumeItemUseCase>().AsCached();
            Container.Bind<GetFragmentLineupUseCase>().AsCached();

            Container.Bind<RandomFragmentBoxWireFrame>().AsCached();

            Container.BindViewWithKernal<RandomFragmentBoxViewController>();
            Container.BindInterfacesTo<RandomFragmentBoxPresenter>().AsCached();

            Container.BindViewFactoryInfo<RandomFragmentLineupViewController, RandomFragmentBoxLineupViewControllerInstaller>();
            Container.BindViewFactoryInfo<FragmentProvisionRatioViewController, FragmentProvisionRatioViewInstaller>();
            Container.BindViewFactoryInfo<ExchangeConfirmViewController, ExchangeConfirmViewControllerInstaller>();
        }
    }
}
