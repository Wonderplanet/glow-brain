using GLOW.Scenes.UnitEnhanceRankUpDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Presenters;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Application.Views
{
    public class UnitEnhanceRankUpDialogViewControllerInstaller : Installer
    {
        [Inject] UnitEnhanceRankUpDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(UnitEnhanceRankUpDialogViewControllerInstaller), "InstallBindings");

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<UnitEnhanceRankUpDialogViewController>();
            Container.BindInterfacesTo<UnitEnhanceRankUpDialogPresenter>().AsCached();
            Container.Bind<UnitEnhanceRankUpDialogUseCase>().AsCached();
        }
    }
}
