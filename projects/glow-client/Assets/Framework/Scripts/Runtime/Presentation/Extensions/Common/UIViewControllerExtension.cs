using UIKit;

namespace WPFramework.Presentation.Extensions
{
    public static class UIViewControllerExtension
    {
        public static void ShowWithKeepHierarchy(this UIViewController self, UIViewController controller, bool animated = true, bool worldPositionStays = true)
        {
            var parent = controller.View.transform.parent;
            self.Show(controller, animated);
            controller.View.transform.SetParent(parent, worldPositionStays);
        }
    }
}
