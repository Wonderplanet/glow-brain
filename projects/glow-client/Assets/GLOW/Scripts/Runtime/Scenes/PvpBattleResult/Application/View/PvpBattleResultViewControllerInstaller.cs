using GLOW.Scenes.PvpBattleResult.Presentation.View;
using GLOW.Scenes.PvpBattleResult.Presentation.Presenter;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Application.View
{
    public class PvpBattleResultViewControllerInstaller : Installer
    {
        [Inject] PvpBattleResultViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpBattleResultViewController>();
            Container.BindInterfacesTo<PvpBattleResultPresenter>().AsCached();
        }
    }
}