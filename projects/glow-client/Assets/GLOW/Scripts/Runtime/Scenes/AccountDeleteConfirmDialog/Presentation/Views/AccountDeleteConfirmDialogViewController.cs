using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Views
{
    public class AccountDeleteConfirmDialogViewController : UIViewController<AccountDeleteConfirmDialogView>, IEscapeResponder
    {
        [Inject] IAccountDeleteConfirmDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

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

        [UIAction]
        void OnAccountDeleteConfirm()
        {
            ViewDelegate.OnAccountDeleteConfirm();
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            if(ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnClose();
            return true;
        }
    }
}
