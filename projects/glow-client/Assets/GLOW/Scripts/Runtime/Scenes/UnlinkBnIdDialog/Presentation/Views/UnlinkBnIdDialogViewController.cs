using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Models;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnlinkBnIdDialog.Presentation.Views
{
    public class UnlinkBnIdDialogViewController : UIViewController<UnlinkBnIdDialogView>, IEscapeResponder
    {
        [Inject] IUnlinkBnIdDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
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

        [UIAction]
        void OnUnlinkBnIdButtonTapped()
        {
            ViewDelegate.OnUnlinkBnId();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            ViewDelegate.OnClose();
            return true;
        }
    }
}
