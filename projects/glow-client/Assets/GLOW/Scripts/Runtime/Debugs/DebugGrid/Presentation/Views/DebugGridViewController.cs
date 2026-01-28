using UIKit;

namespace GLOW.Debugs.DebugGrid.Presentation.Views
{
    public class DebugGridViewController : UIViewController<DebugGridView>
    {
        [UIAction]
        void OnClose()
        {
            Dismiss();
        }
    }
}
