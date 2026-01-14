using GLOW.Scenes.AccountBanDialog.Presentation.Presenter;
using GLOW.Scenes.AccountBanDialog.Presentation.View;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AccountBanDialog.Application.Installers
{
    public class AccountBanDialogViewControllerInstaller : Installer
    {
        [Inject] AccountBanDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AccountBanDialogViewController>();
            Container.BindInterfacesTo<AccountBanDialogPresenter>().AsCached();
            Container.Bind<AccountBanNoticeUseCase>().AsCached();
        }
    }
}
