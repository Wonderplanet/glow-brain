using System;
using UIKit;
using Zenject;

namespace GLOW.Scenes.Splash.Presentation.Views
{
    public sealed class SplashViewController : UIViewController<SplashView>
    {
        [Inject] IISplashViewDelegate SplashViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            SplashViewDelegate.OnViewDidLoad();
        }
        
        public void PlayDisappearAttentionSplashAnimation()
        {
            ActualView.PlayDisappearAttentionSplashAnimation();
        }

        public void SetOnTouchLayerTouched(Action onTapAction)
        {
            ActualView.SetOnTouchLayerTouched(onTapAction);
        }
    }
}
