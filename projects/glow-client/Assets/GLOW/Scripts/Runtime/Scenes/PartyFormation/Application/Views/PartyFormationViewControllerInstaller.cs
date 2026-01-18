using GLOW.Scenes.HomePartyFormation.Domain.Evaluators;
using GLOW.Scenes.HomePartyFormation.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.Presenters;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitSortAndFilterDialog.Application.Installers;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Application.Views
{
    public class PartyFormationViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PartyFormationViewController>();
            Container.BindInterfacesTo<PartyFormationPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitSortAndFilterDialogViewController, UnitSortAndFilterDialogViewInstaller>();

            Container.Bind<GetPartyFormationUnitListUseCase>().AsCached();
            Container.Bind<AssignPartyUnitUseCase>().AsCached();
            Container.Bind<UnassignedPartyUnitUseCase>().AsCached();
            Container.Bind<InitializeTemporaryPartyUseCase>().AsCached();
            Container.Bind<ApplyUpdatedPartyUseCase>().AsCached();
            Container.Bind<UpdatePartyUnitListUseCase>().AsCached();
            Container.Bind<UpdateSelectPartyUseCase>().AsCached();
            Container.Bind<GetNextPartyMemberSlotConditionUseCase>().AsCached();
            Container.Bind<SetupPartyFormationConditionalFilterUseCase>().AsCached();
            Container.Bind<RecommendPartyFormationUseCase>().AsCached();
            
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<RecommendPartyFormationEvaluator>().AsCached();
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();
        }
    }
}
