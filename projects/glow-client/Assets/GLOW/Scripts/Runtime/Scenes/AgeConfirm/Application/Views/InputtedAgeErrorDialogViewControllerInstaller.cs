using GLOW.Scenes.AgeConfirm.Presentation.Presenters;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Application.Views
{
    public class InputtedAgeErrorDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<InputtedAgeErrorDialogViewController>();
            Container.BindInterfacesTo<InputtedAgeErrorDialogPresenter>().AsCached();
        }
    }
}
