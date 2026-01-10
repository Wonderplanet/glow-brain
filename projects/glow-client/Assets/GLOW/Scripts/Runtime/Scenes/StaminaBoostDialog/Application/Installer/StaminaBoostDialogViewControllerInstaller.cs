using GLOW.Scenes.StaminaBoostDialog.Domain.UseCase;
using GLOW.Scenes.StaminaBoostDialog.Presentation.Presenter;
using GLOW.Scenes.StaminaBoostDialog.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.StaminaBoostDialog.Application.Installer
{
    public class StaminaBoostDialogViewControllerInstaller : Zenject.Installer
    {
        [Inject] StaminaBoostDialogViewController.Argument Args { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<StaminaBoostDialogViewController>();
            Container.BindInterfacesTo<StaminaBoostDialogPresenter>().AsCached();
            Container.Bind<StaminaBoostUseCase>().AsCached();
            Container.BindInstance(Args).AsCached();
        }
    }
}
