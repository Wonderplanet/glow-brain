using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Scenes.Notice.Presentation.View;
using GLOW.Scenes.Notice.Presentation.ViewModel;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Notice.Presentation.Facade
{
    public class NoticeViewFacade : INoticeViewFacade
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public async UniTask<NoticeTransitionFlag> ShowInGameNotice(
            IReadOnlyList<NoticeViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            foreach (var viewModel in viewModels)
            {
                NoticeTransitionFlag transitionFlag;
                if (viewModel.ViewType == IgnDisplayType.BasicBanner)
                {
                    transitionFlag = await ShowSimpleBannerInGameNotice(viewModel, cancellationToken);
                }
                else
                {
                    transitionFlag = await ShowDialogTypeInGameNotice(viewModel, cancellationToken);
                }

                // 遷移ボタンがタップされた場合は即座にreturn
                if (transitionFlag)
                {
                    return NoticeTransitionFlag.True;
                }
            }
            // すべての通知が閉じられた場合はFalseを返す
            return NoticeTransitionFlag.False;
        }

        public void ShowInGameNoticeWithBannerDownload(NoticeViewModel viewModel)
        {
            var argument = new NoticeSimpleBannerViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                NoticeSimpleBannerViewController,
                NoticeSimpleBannerViewController.Argument>(argument);
            Canvas.RootViewController.PresentModally(controller);
        }

        async UniTask<NoticeTransitionFlag> ShowSimpleBannerInGameNotice(
            NoticeViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<NoticeTransitionFlag>();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new NoticeSimpleBannerViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                NoticeSimpleBannerViewController,
                NoticeSimpleBannerViewController.Argument>(argument);
            controller.OnCloseCompletion = () =>
            {
                completionSource.TrySetResult(NoticeTransitionFlag.False);
            };

            controller.OnTransitCompletion = () =>
            {
                completionSource.TrySetResult(NoticeTransitionFlag.True);
            };

            Canvas.RootViewController.PresentModally(controller);
            return await completionSource.Task;
        }

        async UniTask<NoticeTransitionFlag> ShowDialogTypeInGameNotice(
            NoticeViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<NoticeTransitionFlag>();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new NoticeDialogViewController.Argument(viewModel);
            var controller = ViewFactory.Create<
                NoticeDialogViewController,
                NoticeDialogViewController.Argument>(argument);
            controller.OnCloseCompletion = () =>
            {
                completionSource.TrySetResult(NoticeTransitionFlag.False);
            };

            controller.OnTransitCompletion = () =>
            {
                completionSource.TrySetResult(NoticeTransitionFlag.True);
            };

            Canvas.RootViewController.PresentModally(controller);
            return await completionSource.Task;
        }
    }
}