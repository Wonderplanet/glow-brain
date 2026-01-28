using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Models;
using UIKit;
using Zenject;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Views
{
    public class TitleLinkBnIdDialogViewController : UIViewController<TitleLinkBnIdDialogView>
    {
        [Inject] ITitleLinkBnIdDialogViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
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
    }
}
