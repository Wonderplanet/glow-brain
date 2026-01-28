using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.LinkBnIdDialog.Presentation.Views
{
    public class LinkBnIdDialogViewController : UIViewController<LinkBnIdDialogView>, IEscapeResponder
    {
        [Inject] ILinkBnIdDialogViewDelegate ViewDelegate { get; }
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
        void OnLinkBnIdButtonTapped()
        {
            ViewDelegate.OnLinkBnId();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnClose();
            return true;
        }
    }
}
