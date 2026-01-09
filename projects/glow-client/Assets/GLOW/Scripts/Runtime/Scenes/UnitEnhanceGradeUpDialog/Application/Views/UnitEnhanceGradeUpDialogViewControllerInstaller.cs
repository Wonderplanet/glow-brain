using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Presenters;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Application.Views
{
    public class UnitEnhanceGradeUpDialogViewControllerInstaller : Installer
    {
        [Inject] UnitEnhanceGradeUpDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(UnitEnhanceGradeUpDialogViewControllerInstaller), "InstallBindings");

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<UnitEnhanceGradeUpDialogViewController>();
            Container.BindInterfacesTo<UnitEnhanceGradeUpDialogPresenter>().AsCached();
            Container.Bind<UnitEnhanceGradeUpDialogUseCase>().AsCached();
        }
    }
}
