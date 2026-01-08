using GLOW.Scenes.AdventBattleResult.Presentation.Presenter;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Application.Installers.View
{
    public class AdventBattleResultViewControllerInstaller : Installer
    {
        [Inject] AdventBattleResultViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<AdventBattleResultViewController>().AsCached();
            Container.BindInterfacesTo<AdventBattleResultPresenter>().AsCached();
        }
    }
}