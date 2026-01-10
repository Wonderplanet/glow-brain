using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Presentation.Views
{
    public class AsyncCommonReceiveViewController : UIViewController<AsyncCommonReceiveView>, IEscapeResponder
    {
        public record Argument(
            Func<CancellationToken, UniTask<IReadOnlyList<CommonReceiveResourceViewModel>>> DataSource,
            Action OnViewClosed,
            Action OnReceivedReward);

        [Inject] ICommonReceiveViewDelegate ViewDelegate { get; }
        [Inject] IAsyncCommonReceiveViewControl AsyncCommonReceiveViewControl { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnPlayerResourceIconTapped = ViewDelegate.OnIconSelected;
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);

            ViewDelegate.OnViewWillAppear();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetupScrollRectSize(IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> iconViewModels)
        {
            ActualView.SetupScrollRectSize(iconViewModels);
        }

        public void ShowAcquiredPlayerResources(IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> iconViewModels, Action onComplete)
        {
            AsyncCommonReceiveViewControl.CanSkipRewardAnimation();
            ActualView.SetAcquiredPlayerResources(iconViewModels, onComplete);
        }

        public async UniTask PlayRewardLabelAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayAnimation(cancellationToken);
        }

        public void UpdateLayout()
        {
            ActualView.UpdateLayout();
        }

        public void ShowLoading()
        {
            ActualView.StartLoading();
        }

        public void SkipAnimation()
        {
            ActualView.SkipAnimation();
        }

        public void ViewClosable()
        {
            AsyncCommonReceiveViewControl.OnListAnimationCompleted();
        }

        public void SetEnableCloseText(bool enable)
        {
            ActualView.SetEnableCloseText(enable);
        }

        public void StopLoadingAnimation()
        {
            ActualView.StopLoading();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnCloseSelected();
            return true;
        }

        [UIAction]
        void OnScreenTapped()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
