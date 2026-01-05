using GLOW.Core.Application.ErrorHandle;
using GLOW.Core.Application.ErrorHandle.Handlers;
using GLOW.Core.Domain.Mappers.Exception;
using GLOW.Modules.CommonWebView.Application.Installers;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Scenes.AccountBanDialog.Application.Installers;
using GLOW.Scenes.AccountBanDialog.Presentation.View;
using GLOW.Scenes.AnnouncementWindow.Application.Installers;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using GLOW.Scenes.ClientUpdate.Application.Installers;
using GLOW.Scenes.ClientUpdate.Presentation.View;
using GLOW.Scenes.MaintenanceDialog.Application.Installers;
using GLOW.Scenes.MaintenanceDialog.Presentation.View;
using WPFramework.Application.Modules;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Applications.ErrorHandle;
#endif // / GLOW_DEBUG

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class GlowErrorHandleInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: エラーダイアログの生成機能をインストール
            //       デバッグ時はデバッグ用のダイアログの生成処理をバインドする
#if GLOW_DEBUG
            Container.BindInterfacesTo<DebugCommonExceptionViewer>().AsCached();
#else
            Container.BindInterfacesTo<CommonExceptionViewer>().AsCached();
#endif
            Container.BindInterfacesTo<AuthenticatorExceptionHandler>().AsCached();
            Container.BindInterfacesTo<UnhandledExceptionHandler>().AsCached();
            Container.BindInterfacesTo<DiskFullExceptionHandler>().AsCached();
            Container.BindInterfacesTo<NetworkExceptionHandler>().AsCached();
            Container.BindInterfacesTo<ServerErrorExceptionHandler>().AsCached();
            Container.BindInterfacesTo<SocketExceptionHandler>().AsCached();
            Container.BindInterfacesTo<TimeoutControlHandler>().AsCached();
            Container.BindInterfacesTo<NetworkUnreachableControlHandler>().AsCached();
            Container.BindInterfacesTo<AddressablesInvalidKeyExceptionHandler>().AsCached();
            Container.BindInterfacesTo<AnnouncementViewFacade>().AsCached();
            Container.BindInterfacesTo<CommonWebViewControl>().AsCached();

            Container.BindViewFactoryInfo<ClientUpdateDialogViewController, ClientUpdateDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<MaintenanceDialogViewController, MaintenanceDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<AnnouncementMainViewController, AnnouncementMainViewControllerInstaller>();
            Container.BindViewFactoryInfo<CommonWebViewController, CommonWebViewControllerInstaller>();
            Container.BindViewFactoryInfo<AccountBanDialogViewController, AccountBanDialogViewControllerInstaller>();

            // NOTE: ServerErrorExceptionの変換処理を読み込む
            Container.BindInterfacesTo<ServerErrorExceptionMapper>().AsCached();
        }
    }
}
