using GLOW.Scenes.EventMission.Presentation.Presenter;
using GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EventMission.Application.Installers.View
{
    public class EventDailyBonusViewControllerInstaller : Installer
    {
        [Inject] EventDailyBonusViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<EventDailyBonusViewController>();
            Container.BindInterfacesTo<EventDailyBonusPresenter>().AsCached();
        }
    }
}