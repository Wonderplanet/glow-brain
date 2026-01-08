using GLOW.Scenes.Community.Application.Installers.Views;
using GLOW.Scenes.Community.Presentation.View;
using GLOW.Scenes.HomeHelpDialog.Application.Views;
using GLOW.Scenes.HomeHelpDialog.Presentation.Views;
using GLOW.Scenes.HomeMenu.Domain.UseCase;
using GLOW.Scenes.HomeMenu.Presentation.Presenter;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.HomeMenuSetting.Application.Views;
using GLOW.Scenes.HomeMenuSetting.Presentation.View;
using GLOW.Scenes.Inquiry.Application.Views;
using GLOW.Scenes.Inquiry.Domain.UseCases;
using GLOW.Scenes.Inquiry.Presentation.View;
using GLOW.Scenes.OtherMenu.Application;
using GLOW.Scenes.OtherMenu.Presentation;
using GLOW.Scenes.UnlinkBnIdDialog.Application.Views;
using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMenu.Application.Views
{
    public class HomeMenuViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMenuViewController>();
            Container.BindInterfacesTo<HomeMenuPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<InquiryDialogViewController, InquiryDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<OtherMenuViewController, OtherMenuViewInstaller>();
            Container.BindViewFactoryInfo<CommunityMenuViewController, CommunityMenuViewControllerInstaller>();
            Container.BindViewFactoryInfo<HomeMenuSettingViewController, HomeMenuSettingViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnlinkBnIdDialogViewController, UnlinkBnIdDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<HomeHelpDialogViewController, HomeHelpDialogViewControllerInstaller>();
            Container.Bind<GetInquiryModelUseCase>().AsCached();
            Container.Bind<LinkedBnIdCheckUseCase>().AsCached();
        }
    }
}
