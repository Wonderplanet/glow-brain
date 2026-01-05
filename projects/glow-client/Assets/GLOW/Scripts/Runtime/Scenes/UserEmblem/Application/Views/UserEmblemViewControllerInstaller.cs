using GLOW.Scenes.UserEmblem.Domain.UseCases;
using GLOW.Scenes.UserEmblem.Presentation.Presenters;
using GLOW.Scenes.UserEmblem.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Application.Views
{
    public class UserEmblemViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(UserEmblemViewControllerInstaller), "InstallBindings");

            Container.BindViewWithKernal<UserEmblemViewController>();
            Container.BindInterfacesTo<UserEmblemPresenter>().AsCached();

            Container.Bind<GetUserEmblemModelUseCase>().AsCached();
            Container.Bind<ApplyUserEmblemUseCase>().AsCached();
            Container.Bind<GetUserEmblemBadgeUseCase>().AsCached();
            Container.Bind<UpdateUserEmblemBadgeUseCase>().AsCached();
        }
    }
}
