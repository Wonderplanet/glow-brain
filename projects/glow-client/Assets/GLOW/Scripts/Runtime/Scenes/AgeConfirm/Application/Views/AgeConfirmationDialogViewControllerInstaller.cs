using GLOW.Scenes.AgeConfirm.Presentation.Presenters;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Application.Views
{
    public class AgeConfirmationDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AgeConfirmationDialogViewController>();
            Container.BindInterfacesTo<AgeConfirmationDialogPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<
                InputtedAgeConfirmationDialogViewController, 
                InputtedAgeConfirmationDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                InputtedAgeErrorDialogViewController, 
                InputtedAgeErrorDialogViewControllerInstaller>();
        }
    }
}
