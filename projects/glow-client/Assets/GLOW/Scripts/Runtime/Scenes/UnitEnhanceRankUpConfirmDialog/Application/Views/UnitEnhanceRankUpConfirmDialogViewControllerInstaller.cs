using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Presenters;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Application.Views
{
    public class UnitEnhanceRankUpConfirmDialogViewControllerInstaller : Installer
    {
        [Inject] UnitEnhanceRankUpConfirmDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitEnhanceRankUpConfirmDialogViewController>();

            Container.BindInterfacesTo<UnitEnhanceRankUpConfirmDialogPresenter>().AsCached();
            Container.Bind<GetUnitEnhanceRankUpConfirmModelUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
