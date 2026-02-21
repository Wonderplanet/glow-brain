using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.Presenters;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Application.Views
{
    public class InputtedAgeConfirmationDialogViewControllerInstaller : Installer
    {
        [Inject] InputtedAgeConfirmationDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<InputtedAgeConfirmationDialogViewController>();
            Container.BindInterfacesTo<InputtedAgeConfirmationDialogPresenter>().AsCached();
            Container.BindInstance(Argument);

            Container.Bind<InputtedAgeConfirmationDialogUseCase>().AsCached();
        }
    }
}
