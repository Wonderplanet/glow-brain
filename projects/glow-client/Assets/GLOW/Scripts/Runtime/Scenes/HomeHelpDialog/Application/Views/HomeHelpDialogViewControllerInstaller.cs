using GLOW.Scenes.HomeHelpDialog.Domain.AssetLoaders;
using GLOW.Scenes.HomeHelpDialog.Presentation.Presenters;
using GLOW.Scenes.HomeHelpDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.HomeHelpDialog.Application.Views
{
    public class HomeHelpDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeHelpDialogViewController>();

            Container.BindInterfacesTo<HomeHelpDialogPresenter>().AsCached();
            Container.BindInterfacesTo<HomeHelpInfoListAssetLoader>().AsCached();
        }
    }
}
