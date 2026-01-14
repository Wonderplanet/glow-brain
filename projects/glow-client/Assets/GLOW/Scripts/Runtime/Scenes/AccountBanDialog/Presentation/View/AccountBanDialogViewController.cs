using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

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
    public class AccountBanDialogViewController : UIViewController<AccountBanDialogView>, IEscapeResponder
    {
        public record Argument(AccountBanType AccountBanType);

        [Inject] IAccountBanDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public Action OnClose { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.ViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void SetContent(AccountBanType accountBanType)
        {
            ActualView.SetContent(accountBanType);
        }

        public void SetUserMyId(UserMyId userMyId)
        {
            ActualView.SetUserMyId(userMyId);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnClose();
            return true;
        }
    }
}
