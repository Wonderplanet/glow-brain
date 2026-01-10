using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.UseCases;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Presenters;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Application.Views
{
    public class OutpostEnhanceLevelUpDialogControllerInstaller : Installer
    {
        [Inject] OutpostEnhanceLevelUpDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(OutpostEnhanceLevelUpDialogControllerInstaller), "InstallBindings");

            Container.BindViewWithKernal<OutpostEnhanceLevelUpDialogViewController>();
            Container.BindInterfacesTo<OutpostEnhanceLevelUpDialogPresenter>().AsCached();
            Container.Bind<GetOutpostEnhanceLevelUpDialogModelUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
