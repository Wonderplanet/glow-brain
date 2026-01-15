using GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Presenters;
using GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AccountDeleteConfirmDialog.Application.Views
{
    public class AccountDeleteConfirmDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<AccountDeleteConfirmDialogViewController>();
            Container.BindInterfacesTo<AccountDeleteConfirmDialogPresenter>().AsCached();
        }
    }
}
