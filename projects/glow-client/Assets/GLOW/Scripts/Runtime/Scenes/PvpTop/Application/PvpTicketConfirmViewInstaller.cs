using GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpTop.Application
{
    public class PvpTicketConfirmViewInstaller : Installer
    {
        [Inject] PvpTicketConfirmViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<PvpTicketConfirmViewController>();
        }
    }
}
