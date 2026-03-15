using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AssetDownloadNotice.Presentation.Views
{
    public interface IAssetDownloadNoticeViewDelegate
    {
        void OnViewDidLoad(AssetDownloadSize downloadSize);
        void OnDownload();
        void OnCancel();
    }
}
