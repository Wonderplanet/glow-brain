using GLOW.Scenes.AssetDownloadNotice.Presentation.Presenters;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AssetDownloadNotice.Application.Views
{
    public class AssetDownloadNoticeViewControllerInstaller : Installer
    {
        [Inject] AssetDownloadNoticeViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindInterfacesTo<AssetDownloadNoticePresenter>().AsCached();
            Container.BindViewWithKernal<AssetDownloadNoticeViewController>();
        }
    }
}
