using System;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.MaintenanceDialog.Presentation.View
{
    public class MaintenanceDialogViewController : UIViewController<MaintenanceDialogView>, IEscapeResponder
    {
        public record Argument(string MessageText);

        [Inject] IMaintenanceDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

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

        public void SetMessage(string message)
        {
            ActualView.SetMessage(message);
        }

        [UIAction]
        void OnOpenSNSPageButtonTapped()
        {
            ViewDelegate.OnOpenSNSPage();
        }

        [UIAction]
        void OnOpenAnnouncementButtonTapped()
        {
            ViewDelegate.OnOpenAnnouncementView();
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
