using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Presenters;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Application.Views
{
    public class UnitEnhanceGradeUpConfirmDialogViewControllerInstaller : Installer
    {
        [Inject] UnitEnhanceGradeUpConfirmDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitEnhanceGradeUpConfirmDialogViewController>();
            Container.BindInterfacesTo<UnitEnhanceGradeUpConfirmDialogPresenter>().AsCached();
            Container.Bind<GetUnitEnhanceGradeUpConfirmModelUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
