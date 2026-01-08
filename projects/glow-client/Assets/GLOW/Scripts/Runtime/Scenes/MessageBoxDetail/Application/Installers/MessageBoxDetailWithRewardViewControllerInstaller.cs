using GLOW.Scenes.MessageBoxDetail.Presentation.Presenter;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Application.Installers
{
    public class MessageBoxDetailWithRewardViewControllerInstaller : Installer
    {
        [Inject] MessageBoxDetailWithRewardViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailWithRewardViewControllerInstaller), "InstallBindings");
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<MessageBoxDetailWithRewardViewController>();
            Container.BindInterfacesTo<MessageBoxDetailWithRewardPresenter>().AsCached();
            
            
        }
    }
}