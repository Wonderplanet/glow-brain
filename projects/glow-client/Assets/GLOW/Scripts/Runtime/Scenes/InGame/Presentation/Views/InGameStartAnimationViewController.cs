using System;
using UIKit;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public class InGameStartAnimationViewController : UIViewController<InGameStartAnimationView>
    {
        public record Argument(Action OnViewClosed);

        [Inject] IInGameStartAnimationViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnCompleted = ViewDelegate.OnAnimationCompleted;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }
    }
}
