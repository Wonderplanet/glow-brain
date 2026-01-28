using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class InGameDefeatResultViewControllerInstaller : Installer
    {
        [Inject] DefeatResultViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<DefeatResultViewController>();
            Container.BindInterfacesTo<DefeatResultPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
        }
    }
}
