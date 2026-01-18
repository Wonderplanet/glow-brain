using GLOW.Scenes.Home.Domain.Constants;
using UIKit;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class ContextFirstViewController<T> where T : UIViewController
    {
        public HomeContentTypes ContentType { get; }
        public T ViewController { get; }
        public HomeContentDisplayType HomeContentDisplayType { get; }

        public ContextFirstViewController(
            HomeContentTypes contentType,
            T viewController,
            HomeContentDisplayType homeContentDisplayType)
        {
            ContentType = contentType;
            ViewController = viewController;
            HomeContentDisplayType = homeContentDisplayType;
        }
    }
}
