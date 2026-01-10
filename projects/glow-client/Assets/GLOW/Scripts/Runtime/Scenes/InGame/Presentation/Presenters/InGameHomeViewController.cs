using System;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Views;
using UIKit;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    /// <summary>
    /// InGame用のHomeViewControllerダミー実装
    /// InGameシーンではHomeViewControllerの機能をInGameViewDelegateに委譲する
    /// </summary>
    public class InGameHomeViewController : IHomeViewController
    {
        [Inject] IInGameViewDelegate InGameViewDelegate { get; }
        
        public void PresentModally(UIViewController controller, bool animated = true, Action completion = null)
        {
            InGameViewDelegate.PresentModally(controller, animated, completion);
        }
    }
}

