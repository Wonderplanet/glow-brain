using GLOW.Scenes.UserNameEdit.Application.Views;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using GLOW.Scenes.UserProfile.Domain.Models;
using GLOW.Scenes.UserProfile.Domain.UseCases;
using GLOW.Scenes.UserProfile.Presentation.Presenters;
using GLOW.Scenes.UserProfile.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UserProfile.Application.Views
{
    public class UserProfileViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(UserProfileViewControllerInstaller), "InstallBindings");

            Container.BindViewWithKernal<UserProfileViewController>();
            Container.BindInterfacesTo<UserProfilePresenter>().AsCached();

            Container.Bind<GetUserProfileModelUseCase>().AsCached();
            Container.Bind<ApplyUserAvatarUseCase>().AsCached();
            Container.Bind<GetUserProfileAvatarBadgeUseCase>().AsCached();
            Container.Bind<UpdateUserProfileAvatarBadgeUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();

            Container.BindViewFactoryInfo<UserNameEditDialogViewController, UserNameEditDialogViewControllerInstaller>();
        }
    }
}
