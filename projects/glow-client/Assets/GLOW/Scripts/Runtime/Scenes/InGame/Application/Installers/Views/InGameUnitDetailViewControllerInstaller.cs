using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGameUnitDetailViewControllerInstaller : Installer
    {
        [Inject] InGameUnitDetailViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<InGameUnitDetailViewController>();
            Container.BindInterfacesTo<InGameUnitDetailPresenter>().AsCached();
            Container.Bind<InGameUnitDetailUseCase>().AsCached();

            Container.BindInterfacesTo<UnitEnhanceAbilityModelListFactory>().AsCached();
        }
    }
}
