using GLOW.Scenes.Home.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers
{
    public class QuestReleaseViewInstaller : Installer
    {
        [Inject] QuestReleaseViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<QuestReleaseViewController>();

        }
    }
}
