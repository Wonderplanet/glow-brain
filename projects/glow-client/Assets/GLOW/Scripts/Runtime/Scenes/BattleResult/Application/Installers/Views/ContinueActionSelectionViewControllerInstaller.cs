using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PassShop.Domain.UseCase;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class ContinueActionSelectionViewControllerInstaller : Installer
    {
        [Inject] ContinueActionSelectionViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.BindViewWithKernal<ContinueActionSelectionViewController>();
            Container.BindInterfacesTo<ContinueActionSelectionPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<ContinueDiamondViewController, ContinueDiamondViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondPurchaseViewController, DiamondPurchaseViewControllerInstaller>();

            Container.BindInterfacesTo<HeldAdSkipPassInfoModelFactory>().AsCached();
            Container.Bind<GetHeldAdSkipPassInfoUseCase>().AsCached();

        }
    }
}
