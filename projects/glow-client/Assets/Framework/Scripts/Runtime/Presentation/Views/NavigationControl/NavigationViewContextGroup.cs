using System.Collections.Generic;
using UIKit;

namespace WPFramework.Presentation.Views
{
    public struct NavigationViewContextGroup
    {
        public INavigationViewContext Context
        {
            get;
        }

        public IReadOnlyList<UIViewController> ControllerList
        {
            get;
        }

        public NavigationViewContextGroup(INavigationViewContext context, IReadOnlyList<UIViewController> controllerList)
        {
            Context = context;
            ControllerList = controllerList;
        }
    }
}
