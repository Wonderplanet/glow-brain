using System;
using UIKit;

namespace WPFramework.Presentation.Views
{
    public interface IViewNavigation
    {
        INavigationViewContext CurrentContext { get; }

        void Push(UIViewController controller, bool animated = true, Action completion = null);
        void Push(UIViewController controller, INavigationViewContext context, bool animated = true, Action completion = null);
        void Pop(bool animated = true, Action completion = null);
        void PopToRootAndPush(UIViewController controller, bool animated = true, Action completion = null);
        void PopToRootAndPush(UIViewController controller, INavigationViewContext context, bool animated = true, Action completion = null);
    }
}
