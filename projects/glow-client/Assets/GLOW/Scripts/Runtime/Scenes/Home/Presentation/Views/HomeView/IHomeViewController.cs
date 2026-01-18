using System;
using UIKit;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeViewController
    {
        void PresentModally(UIViewController controller, bool animated = true, Action completion = null);
    }
}

