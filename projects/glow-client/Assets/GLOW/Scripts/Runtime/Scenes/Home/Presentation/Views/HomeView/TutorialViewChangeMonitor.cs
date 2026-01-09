using UIKit;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class TutorialViewChangeMonitor
    {
        UIViewController _currentViewController;
        public UIViewController CurrentViewController => _currentViewController;

        public void SetCurrentViewController(UIViewController controller)
        {
            _currentViewController = controller;
        }
    }
}