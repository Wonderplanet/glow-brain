using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Presentation.Presenters
{
    public class CommonReceiveWireFrame
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }
        
        // 表示しているAsyncCommonReceiveViewController
        UIViewController _presentingAsyncCommonReceiveViewController;

        public void Show(
            IReadOnlyList<CommonReceiveResourceViewModel> models,
            RewardTitle rewardTitle = null,
            ReceivedRewardDescription receivedRewardDescription = null,
            Action onClosed = null)
        {
            rewardTitle ??= RewardTitle.Default;
            receivedRewardDescription ??= ReceivedRewardDescription.Empty;

            var groupingSameItems = PlayerResourceMerger.MergeCommonReceiveResourceModel(models);

            var argument = new CommonReceiveViewController.Argument(groupingSameItems, rewardTitle, receivedRewardDescription);
            var controller = ViewFactory.Create<CommonReceiveViewController, CommonReceiveViewController.Argument>(argument);
            if(onClosed != null) controller.OnViewClosed = onClosed;
            Canvas.RootViewController.PresentModally(controller);
        }

        public async UniTask ShowAsync(
            CancellationToken cancellationToken,
            IReadOnlyList<CommonReceiveResourceViewModel> models,
            RewardTitle rewardTitle,
            ReceivedRewardDescription receivedRewardDescription)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var groupingSameItems = PlayerResourceMerger.MergeCommonReceiveResourceModel(models);
            var argument = new CommonReceiveViewController.Argument(groupingSameItems, rewardTitle, receivedRewardDescription);
            var controller = ViewFactory.Create<CommonReceiveViewController, CommonReceiveViewController.Argument>(argument);
            controller.OnViewClosed = () =>
            {
                completionSource.TrySetResult();
            };

            Canvas.RootViewController.PresentModally(controller);

            await completionSource.Task;
        }

        public void AsyncShowReceived(
            Func<CancellationToken, UniTask<IReadOnlyList<CommonReceiveResourceViewModel>>> dataSource,
            Action onClosed,
            Action onReceivedReward = null)
        {
            var argument = new AsyncCommonReceiveViewController.Argument(
                dataSource,
                onClosed,
                onReceivedReward);
            var controller = ViewFactory.Create<
                AsyncCommonReceiveViewController,
                AsyncCommonReceiveViewController.Argument>(argument);
            Canvas.RootViewController.PresentModally(controller);
            _presentingAsyncCommonReceiveViewController = controller;
        }

        public void DismissDisplayedAsyncCommonReceive()
        {
            // AsyncCommonReceive側で上にエラーダイアログが表示されている状態でエラーダイアログを閉じると同時に閉じさせるようにはできないので
            // 呼び出している側で閉じれる状態にする
            _presentingAsyncCommonReceiveViewController?.Dismiss();
            _presentingAsyncCommonReceiveViewController = null;
        }
    }
}
