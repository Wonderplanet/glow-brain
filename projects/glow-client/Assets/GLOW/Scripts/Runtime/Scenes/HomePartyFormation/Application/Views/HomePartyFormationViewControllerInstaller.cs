using GLOW.Scenes.HomePartyFormation.Domain.Evaluators;
using GLOW.Scenes.HomePartyFormation.Domain.UseCases;
using GLOW.Scenes.HomePartyFormation.Presentation.Presenters;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.PartyFormation.Application.Views;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.Presenters;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.PartyNameEdit.Application.Views;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Application.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Application.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Application.Installers;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.HomePartyFormation.Application.Views
{
    public class HomePartyFormationViewControllerInstaller : Installer
    {
        [Inject] HomePartyFormationViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindInterfacesTo<PartyFormationPresenter>().AsCached();
            Container.BindInterfacesTo<HomePartyFormationPresenter>().AsCached();
            Container.Bind<HomePartyFormationUseCase>().AsCached();
            Container.BindViewWithKernal<HomePartyFormationViewController>();

            Container.Bind<GetPartyFormationUnitListUseCase>().AsCached();
            Container.Bind<AssignPartyUnitUseCase>().AsCached();
            Container.Bind<UnassignedPartyUnitUseCase>().AsCached();
            Container.Bind<InitializeTemporaryPartyUseCase>().AsCached();
            Container.Bind<ApplyUpdatedPartyUseCase>().AsCached();
            Container.Bind<UpdatePartyUnitListUseCase>().AsCached();
            Container.Bind<UpdateSelectPartyUseCase>().AsCached();
            Container.Bind<UpdateUnitListFilterUseCase>().AsCached();
            Container.Bind<GetNextPartyMemberSlotConditionUseCase>().AsCached();
            Container.Bind<SetupPartyFormationConditionalFilterUseCase>().AsCached();
            Container.Bind<RecommendPartyFormationUseCase>().AsCached();

            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<RecommendPartyFormationEvaluator>().AsCached();
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<PartyNameEditDialogViewController, PartyNameEditDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<PartyFormationPartyViewController, PartyFormationPageContentControllerInstaller>();
            Container.BindViewFactoryInfo<PartyFormationOneLinePartyViewController, PartyFormationOneLinePartyViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitViewController, UnitEnhanceViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitLevelUpDialogViewController, UnitLevelUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceRankUpDialogViewController, UnitEnhanceRankUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceGradeUpDialogViewController, UnitEnhanceGradeUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitSortAndFilterDialogViewController, UnitSortAndFilterDialogViewInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceRankUpDetailDialogViewController, UnitEnhanceRankUpDetailDialogViewControllerInstaller>();
        }
    }
}
