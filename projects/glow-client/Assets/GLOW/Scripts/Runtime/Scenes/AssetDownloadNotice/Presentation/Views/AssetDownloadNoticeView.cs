using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AssetDownloadNotice.Presentation.Views
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-4_アセットダウンロードダイアログ
    /// </summary>
    public class AssetDownloadNoticeView : UIView
    {
        [SerializeField] UIText _downloadSizeText;

        public void SetDownloadSizeText(AssetDownloadSize downloadSize)
        {
            _downloadSizeText.SetText("<color=#000>タイトル画面でダウンロードを行います。\n(サイズ </color>{0}<color=#000>)</color>", downloadSize.ToStringSeparated());
        }
    }
}
