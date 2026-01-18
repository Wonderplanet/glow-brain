using System;
using GLOW.Core.Domain.ValueObjects;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AssetDownloadNotice.Presentation.Views
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-4_アセットダウンロードダイアログ
    /// </summary>
    public class AssetDownloadNoticeViewController :  UIViewController<AssetDownloadNoticeView>, IEscapeResponder
    {
        public record Argument(AssetDownloadSize DownloadSize, Action Download, Action Cancel)
        {
            public AssetDownloadSize DownloadSize { get; } = DownloadSize;
            public Action Download { get; } = Download;
            public Action Cancel { get; } = Cancel;
        }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IAssetDownloadNoticeViewDelegate ViewDelegate { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] Argument Argc { get; set; }


        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(Argc.DownloadSize);
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            // PresentModallyで表示するとき、モーダルのバックキーより後に設定したいのでWillAppearでBindする
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void UpdateView(AssetDownloadSize downloadSize)
        {
            ActualView.SetDownloadSizeText(downloadSize);
        }

        public void NotifyDownload()
        {
            Argc.Download?.Invoke();
        }

        public void NotifyCancel()
        {
            Argc.Cancel?.Invoke();
        }

        [UIAction]
        void OnDownload()
        {
            ViewDelegate.OnDownload();
        }

        [UIAction]
        void OnCancel()
        {
            ViewDelegate.OnCancel();
        }

        bool IEscapeResponder.OnEscape()
        {
            if(View.Hidden)
            {
                return false;
            }

            SystemSoundEffectProvider.PlaySeTap();
            // esc入力を受け付けたら「Cancel」の挙動をとる
            ViewDelegate.OnCancel();

            return true;
        }
    }
}
