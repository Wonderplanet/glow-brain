using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.Presenters;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Application.Views
{
    public class PartyFormationOneLinePartyViewControllerInstaller : Installer
    {
        [Inject] PartyFormationPartyViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PartyFormationOneLinePartyViewController>();
            Container.BindInterfacesTo<PartyFormationPartyPresenter>().AsCached();
            Container.Bind<GetPartyFormationPartyUseCase>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
