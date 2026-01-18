using GLOW.Modules.CommonWebView.Application.Installers;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Scenes.AccountDeleteConfirmDialog.Application.Views;
using GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Views;
using GLOW.Scenes.AppAppliedBalanceDialog.Application;
using GLOW.Scenes.AppAppliedBalanceDialog.Presentation;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.OtherMenu.Presentation;
using GLOW.Scenes.OtherMenu.Domain;
using GLOW.Scenes.PrivacyOptionDialog.Application.Installers;
using GLOW.Scenes.PrivacyOptionDialog.Domain.UseCases;
using GLOW.Scenes.PrivacyOptionDialog.Presentation.Views;
using GLOW.Scenes.PurchaseLimitDialog.Application;
using GLOW.Scenes.PurchaseLimitDialog.Presentation;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.OtherMenu.Application
{
    public class OtherMenuViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<OtherMenuViewController>();
            Container.BindInterfacesTo<OtherMenuPresenter>().AsCached();
            Container.Bind<OtherMenuUseCase>().AsCached();
            Container.Bind<GetDeleteAccountUrlUseCase>().AsCached();
            Container.Bind<PrivacyOptionDialogConsentRequestUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindInterfacesTo<CommonWebViewControl>().AsCached();
            Container.BindViewFactoryInfo<CommonWebViewController, CommonWebViewControllerInstaller>();
            Container.BindViewFactoryInfo<AppAppliedBalanceDialogViewController, AppAppliedBalanceDialogViewInstaller>();
            Container.BindViewFactoryInfo<PurchaseLimitDialogViewController, PurchaseLimitDialogViewInstaller>();
            Container.BindViewFactoryInfo<PrivacyOptionDialogViewController, PrivacyOptionDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<AccountDeleteConfirmDialogViewController, AccountDeleteConfirmDialogViewControllerInstaller>();
        }
    }
}
