using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Application.Views
{
    public class GachaDetailCautionWebViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<GachaDetailCautionWebViewController>();
        }
    }
}