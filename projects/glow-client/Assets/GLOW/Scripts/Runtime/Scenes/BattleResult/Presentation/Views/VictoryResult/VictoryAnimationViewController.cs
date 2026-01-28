using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.CommonToast.Presentation;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// </summary>
    public class VictoryAnimationViewController : UIViewController<VictoryAnimationView>, IEscapeResponder
    {
        public record Argument(Action OnViewClosed);

        [Inject] IVictoryAnimationViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnCompleted = ViewDelegate.OnAnimationCompleted;
            EscapeResponderRegistry.Bind(this, ActualView);

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayCloseAnimation(cancellationToken);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }

        [UIAction]
        void OnScreenTapped()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
