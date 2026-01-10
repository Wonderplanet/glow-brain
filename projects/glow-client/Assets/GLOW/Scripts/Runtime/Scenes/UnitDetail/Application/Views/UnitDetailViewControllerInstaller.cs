using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitDetail.Domain.UseCases;
using GLOW.Scenes.UnitDetail.Presentation.Presenters;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.UnitDetail.Application.Views
{
    public class UnitDetailViewControllerInstaller : Installer
    {
        [Inject] UnitDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitDetailViewController>();

            Container.BindInstance(Argument).AsCached();
            Container.BindInterfacesTo<UnitDetailPresenter>().AsCached();

            Container.Bind<GetUnitMaxStatusDetailUseCase>().AsCached();
            Container.Bind<GetUnitMinimumStatusDetailUseCase>().AsCached();

            Container.BindInterfacesTo<UnitEnhanceAbilityModelListFactory>().AsCached();

            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
        }
    }
}
