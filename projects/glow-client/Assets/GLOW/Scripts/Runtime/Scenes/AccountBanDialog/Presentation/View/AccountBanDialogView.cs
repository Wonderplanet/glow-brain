using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AccountBanDialog.Presentation.View
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-7_アカウント停止ダイアログ
    ///
    /// 800-1-3_BANメッセージ
    /// 800-1-4_BANメッセージ
    /// </summary>
    public class AccountBanDialogView : UIView
    {
        [SerializeField] UIText _userIdText;
        [SerializeField] UIText _headerComment;
        [SerializeField] UIObject _footerComment;

        public void SetContent(AccountBanType accountBanType)
        {
            switch (accountBanType)
            {
                case AccountBanType.Permanent:
                    SetPermanentContent();
                    break;
                case AccountBanType.TemporaryByCheating:
                    SetTemporaryByCheatingContent();
                    break;
                case AccountBanType.TemporaryByDetectedAnomaly:
                    SetTemporaryByDetectedAnomalyContent();
                    break;
                case AccountBanType.TemporaryByUserAccountRefunding:
                    SetTemporaryByUserAccountRefundingContent();
                    break;
            }

            _userIdText.SetText("お客様のID : 取得中です");
        }

        void SetPermanentContent()
        {
            _headerComment.SetText(
                "お客様のアカウントにて、迷惑行為、妨害行為を確認しましたので、お客様のアカウントの永久停止を行います。ゲームをプレイすることはできません。\n"
                + "本通知に関する詳細は、公式ホームページよりお問い合わせ下さい。");
            _headerComment.gameObject.SetActive(true);
            _footerComment.Hidden = true;
        }

        void SetTemporaryByCheatingContent()
        {
            _headerComment.SetText(
                "現在、本アカウントにつきまして、\n"
                + "不正行為を検知したため\n"
                + "アカウントBANを実施しております。");
            _headerComment.gameObject.SetActive(true);
            _footerComment.Hidden = false;
        }

        void SetTemporaryByDetectedAnomalyContent()
        {
            _headerComment.SetText(
                "現在、本アカウントにつきまして、\n"
                + "異常なデータを検知したため\n"
                + "アカウントを停止させて\n"
                + "いただいております。");
            _headerComment.gameObject.SetActive(true);
            _footerComment.Hidden = false;
        }

        void SetTemporaryByUserAccountRefundingContent()
        {
            _headerComment.SetText(
                "お客様のアカウントについて、返金対応のお申し出がございました。\n"
                + "返金対応を行なうため、一時的にアカウントへのログインを制限しております。\n"
                + "\n"
                + "本通知に関するご質問は、公式ホームページよりお問い合わせ下さい。");
            _headerComment.gameObject.SetActive(true);
            _footerComment.Hidden = true;
        }

        public void SetUserMyId(UserMyId userMyId)
        {
            _userIdText.SetText("お客様のID : " + userMyId.Value);
        }
    }
}
