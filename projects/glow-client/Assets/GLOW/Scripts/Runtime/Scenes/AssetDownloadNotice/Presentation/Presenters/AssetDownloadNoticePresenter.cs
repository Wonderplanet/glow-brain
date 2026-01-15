using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.AssetDownloadNotice.Presentation.Presenters
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-4_アセットダウンロードダイアログ
    /// </summary>
    public class AssetDownloadNoticePresenter : IAssetDownloadNoticeViewDelegate
    {
        [Inject] AssetDownloadNoticeViewController ViewController { get; set; }

        void IAssetDownloadNoticeViewDelegate.OnViewDidLoad(AssetDownloadSize downloadSize)
        {
            UpdateView(downloadSize);
        }

        void IAssetDownloadNoticeViewDelegate.OnDownload()
        {
            ViewController.Dismiss(completion: () => ViewController.NotifyDownload());
        }

        void IAssetDownloadNoticeViewDelegate.OnCancel()
        {
            ViewController.Dismiss(completion: () => ViewController.NotifyCancel());
        }

        void UpdateView(AssetDownloadSize downloadSize)
        {
            // NOTE: ビューの更新（空き容量が変化するかもしれないので）
            ViewController.UpdateView(downloadSize);
        }
    }
}
