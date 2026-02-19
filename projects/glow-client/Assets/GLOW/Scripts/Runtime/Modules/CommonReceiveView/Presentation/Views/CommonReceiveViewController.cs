using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Presentation.Views
{
    public class CommonReceiveViewController : UIViewController<CommonReceiveView>, IEscapeResponder
    {
        public record Argument(
            IReadOnlyList<CommonReceiveResourceViewModel> CommonReceiveResourceViewModels,
            RewardTitle RewardTitle,
            ReceivedRewardDescription ReceivedRewardDescription);

        public Action OnViewClosed { get; set; }

        [Inject] ICommonReceiveViewDelegate ViewDelegate { get; }

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
            ActualView.SetAcquiredPlayerResources(iconViewModels, onComplete);
        }

        public void ShowCloseText()
        {
            ActualView.SetEnableCloseText(true);
        }

        public void SkipAnimation()
        {
            ActualView.SkipAnimation();
        }

        public void SetRewardTitleText(RewardTitle rewardTitle)
        {
            ActualView.SetRewardTitleText(rewardTitle);
        }

        public void SetRewardDescriptionText(ReceivedRewardDescription receivedRewardDescription)
        {
            ActualView.SetDescriptionText(receivedRewardDescription);
        }

        public async UniTask FadeInDescriptionText(CancellationToken cancellationToken)
        {
            await ActualView.FadeInDescriptionText(cancellationToken);
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
