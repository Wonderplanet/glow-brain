using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.DailyBonusMission;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Mission.Application.Installers.View
{
    public class DailyBonusMissionViewControllerInstaller: Installer
    {
        [Inject] DailyBonusMissionViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(DailyBonusMissionViewControllerInstaller), nameof(InstallBindings));

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<DailyBonusMissionViewController>();
            Container.BindInterfacesTo<DailyBonusMissionPresenter>().AsCached();
            
            Container.Bind<AutoReceiveDailyBonusUseCase>().AsCached();
        }
    }
}
