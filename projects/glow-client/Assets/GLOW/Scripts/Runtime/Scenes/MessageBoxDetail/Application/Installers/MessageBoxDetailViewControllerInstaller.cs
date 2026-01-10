using GLOW.Scenes.MessageBoxDetail.Presentation.Presenter;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Application.Installers
{
    public class MessageBoxDetailViewControllerInstaller : Installer
    {
        [Inject] MessageBoxDetailViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailViewControllerInstaller), "InstallBindings");
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<MessageBoxDetailViewController>();
            Container.BindInterfacesTo<MessageBoxDetailPresenter>().AsCached();
        }
    }
}