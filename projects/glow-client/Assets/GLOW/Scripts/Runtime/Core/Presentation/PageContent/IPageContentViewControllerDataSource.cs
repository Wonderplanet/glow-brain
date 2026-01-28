using UIKit;

namespace GLOW.Core.Presentation.PageContent
{
    public interface IPageContentViewControllerDataSource
    {
        UIViewController ViewControllerBefore(PageContentViewController pageViewController, UIViewController viewController);
        UIViewController ViewControllerAfter(PageContentViewController pageViewController, UIViewController viewController);
    }
}
