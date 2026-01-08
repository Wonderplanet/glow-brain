using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.UserLevelUp.Application.Installer.View;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class InGameVictoryResultViewControllerInstaller : Installer
    {
        [Inject] VictoryResultViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<VictoryResultViewController>();
            Container.BindInterfacesTo<VictoryResultPresenter>().AsCached();
        }
    }
}
