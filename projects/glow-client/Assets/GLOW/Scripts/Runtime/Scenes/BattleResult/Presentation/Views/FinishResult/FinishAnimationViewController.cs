using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.CommonToast.Presentation;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10_降臨バトル専用バトルリザルト画面
    ///
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-6-1_ コイン獲得クエスト専用バトルリザルト演出、バトル終了時演出など
    /// </summary>
    public class FinishAnimationViewController : UIViewController<FinishAnimationView>, IEscapeResponder
    {
        public record Argument(Action OnViewClosed);

        [Inject] IFinishAnimationViewDelegate ViewDelegate { get; }
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
