using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Application.Views
{
    internal sealed class GachaDetailWebViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<GachaDetailAnnouncementWebViewController>();
        }
    }
}
