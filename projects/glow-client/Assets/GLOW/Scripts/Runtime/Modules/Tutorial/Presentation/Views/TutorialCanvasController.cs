using UIKit;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialCanvasController : UICanvasController
    {
        public TutorialCanvasController()
        {
            PrefabName = "TutorialCanvas";
            ActualView.RootViewController = this;
        }

        public void Present(UIViewController viewController)
        {
            PresentChildController(viewController);
        }
    }
}
