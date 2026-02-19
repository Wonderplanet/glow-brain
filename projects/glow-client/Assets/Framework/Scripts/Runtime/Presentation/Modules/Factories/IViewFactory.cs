using UIKit;

namespace WPFramework.Presentation.Modules
{
    public interface IViewFactory
    {
        T Create<T>(string contextId = null) where T : UIViewController;
        T Create<T, A1>(A1 args1, string contextId = null) where T : UIViewController;
    }
}
