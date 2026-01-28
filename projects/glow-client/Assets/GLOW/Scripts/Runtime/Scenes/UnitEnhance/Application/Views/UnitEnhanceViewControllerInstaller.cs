using GLOW.Core.Data.Services;
using GLOW.Core.Domain.Calculator;
using GLOW.Scenes.UnitEnhance.Domain.UseCases;
using GLOW.Scenes.UnitEnhance.Presentation.Presentations;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Modules.UnitAvatarPageView.Application.Views;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Application.Views
{
    public class UnitEnhanceViewControllerInstaller : Installer
    {
        [Inject] UnitViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitViewController>();
            Container.BindInterfacesTo<UnitEnhancePresenter>().AsCached();
            Container.BindInstance(Argument).AsCached();

            Container.Bind<GetUnitEnhanceLevelUpUseCase>().AsCached();
            Container.Bind<GetUnitEnhanceUnitInfoUseCase>().AsCached();
            Container.Bind<GetUnitEnhanceGradeUpUseCase>().AsCached();
            Container.Bind<ExecuteUnitRankUpUseCase>().AsCached();
            Container.Bind<GetUnitEnhanceSpecialAttackInfoUseCase>().AsCached();
            Container.Bind<ExecuteGradeUpUseCase>().AsCached();
            Container.Bind<GetUnitEnhanceAvatarListUseCase>().AsCached();
            Container.BindInterfacesTo<UnitService>().AsCached();
            Container.BindInterfacesTo<UnitEnhanceRankUpCostCalculator>().AsCached();

            Container.BindInterfacesTo<UnitEnhanceAbilityModelListFactory>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitAvatarPageViewController, UnitAvatarPageViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceRankUpConfirmDialogViewController, UnitEnhanceRankUpConfirmDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceGradeUpConfirmDialogViewController, UnitEnhanceGradeUpConfirmDialogViewControllerInstaller>();
        }
    }
}
