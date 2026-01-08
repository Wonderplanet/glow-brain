using GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PackShop.Application.Views
{
    public class StageClearPackPageContentViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<StageClearPackPageContentViewController>();
        }
    }
}
