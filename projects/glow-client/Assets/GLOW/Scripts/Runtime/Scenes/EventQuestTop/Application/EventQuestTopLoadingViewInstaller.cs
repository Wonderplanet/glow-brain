using GLOW.Scenes.EventQuestTop.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Application
{
    public class EventQuestTopLoadingViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EventQuestTopLoadingViewController>();
        }
    }
}