using GLOW.Scenes.AdventBattleResult.Presentation.Presenter;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Application.Installers.View
{
    public class AdventBattleResultRankUpEffectViewControllerInstaller : Installer
    {
        [Inject] AdventBattleResultRankUpEffectViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.Bind<AdventBattleResultRankUpEffectViewController>().AsCached();
            Container.BindInterfacesTo<AdventBattleResultRankUpEffectPresenter>().AsCached();
        }
    }
}