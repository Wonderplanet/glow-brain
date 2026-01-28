using UIKit;

namespace GLOW.Scenes.PurchaseLimitDialog.Presentation
{
    public class PurchaseLimitDialogViewController : UIViewController<PurchaseLimitDialogView>
    {
        [UIAction]
        void OnClose()
        {
            this.Dismiss();
        }
    }
}
