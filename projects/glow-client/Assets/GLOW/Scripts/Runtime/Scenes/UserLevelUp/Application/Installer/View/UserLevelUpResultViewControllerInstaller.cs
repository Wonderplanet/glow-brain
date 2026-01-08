using GLOW.Scenes.UserLevelUp.Presentation.Presenter;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UserLevelUp.Application.Installer.View
{
    public class UserLevelUpResultViewControllerInstaller : Zenject.Installer
    {
        [Inject] UserLevelUpViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<UserLevelUpViewController>();
            Container.BindInterfacesTo<UserLevelUpResultPresenter>().AsCached();
        }
    }
}