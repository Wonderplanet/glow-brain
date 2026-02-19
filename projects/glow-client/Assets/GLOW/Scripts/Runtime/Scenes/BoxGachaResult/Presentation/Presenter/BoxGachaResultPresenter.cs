using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.BoxGachaResult.Presentation.View;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGachaResult.Presentation.Presenter
{
    public class BoxGachaResultPresenter : IBoxGachaResultViewDelegate
    {
        [Inject] BoxGachaResultViewController ViewController { get; }
        [Inject] BoxGachaResultViewController.Argument Argument { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] UnreceivedRewardWireframe UnreceivedRewardWireframe { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        
        CancellationToken BoxGachaResultCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        void IBoxGachaResultViewDelegate.OnViewDidLoad()
        {
            DoAsync.Invoke(BoxGachaResultCancellationToken, async cancellationToken =>
            {
                ViewController.SetUpResult(Argument.ViewModel);
                await ViewController.PlayOpenAnimation(cancellationToken);
                    
                // 所持上限超過で受け取れなかった報酬がある場合は、その旨を表示
                await ShowUnreceivedRewardIfOverflowDiscarded(cancellationToken);
                
                // メールに送られた報酬がある場合は、その旨を表示
                await ShowUnreceivedRewardIfSentToMessage(cancellationToken);
                
                ViewController.StartAnimation(); 
            });
        }

        void IBoxGachaResultViewDelegate.OnIconCellTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void IBoxGachaResultViewDelegate.OnCloseButtonTapped()
        {
            DoAsync.Invoke(BoxGachaResultCancellationToken, async cancellationToken =>
            {
                await ViewController.PlayCloseAnimation(cancellationToken);
                ViewController.Dismiss();
            });
        }

        void IBoxGachaResultViewDelegate.ShowReceivedUnitAndArtworkIfNeeded()
        {
            DoAsync.Invoke(BoxGachaResultCancellationToken, async cancellationToken =>
            {
                // 受け取ったユニットの受け取り画面を表示(新規ユニットはAvatarViewModelsに入る)
                await ShowReceivedUnits(
                    Argument.ViewModel.AvatarViewModels,
                    cancellationToken);
                
                // 原画獲得演出を表示
                await ShowArtworkFragmentAcquisitionView(
                    Argument.ViewModel.ArtworkFragmentAcquisitionViewModels,
                    cancellationToken);
            });
        }
        
        async UniTask ShowUnreceivedRewardIfSentToMessage(CancellationToken cancellationToken)
        {
            var unreceivedRewardReasonTypeByDrawnResult = 
                Argument.ViewModel.UnreceivedRewardReasonTypeByDrawnResult;
            if (!unreceivedRewardReasonTypeByDrawnResult.Contains(UnreceivedRewardReasonType.SentToMessage))
            {
                return;
            }
            
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            UnreceivedRewardWireframe.ShowSentToMailbox(
                () => completionSource.TrySetResult());
            
            await completionSource.Task;
        }

        async UniTask ShowUnreceivedRewardIfOverflowDiscarded(CancellationToken cancellationToken)
        {
            var unreceivedRewardReasonTypeByDrawnResult = 
                Argument.ViewModel.UnreceivedRewardReasonTypeByDrawnResult;
            if (!unreceivedRewardReasonTypeByDrawnResult.Contains(UnreceivedRewardReasonType.ResourceOverflowDiscarded))
            {
                return;
            }
            
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            UnreceivedRewardWireframe.ShowResourceOverflowDiscarded(
                () => completionSource.TrySetResult());
            
            await completionSource.Task;
        }
        
        async UniTask ShowReceivedUnits(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            var receivedUnitIds = viewModels
                .Where(model => model.ResourceType == ResourceType.Unit)
                .Select(model => model.Id)
                .ToList();

            await UnitReceiveWireFrame.ShowReceivedUnits(
                receivedUnitIds,
                ViewController,
                cancellationToken);
        }
        
        async UniTask ShowArtworkFragmentAcquisitionView(
            IReadOnlyList<ArtworkFragmentAcquisitionViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            foreach (var viewModel in viewModels)
            {
                await ShowArtworkFragmentAcquisitionView(viewModel, cancellationToken);
            }
        }
        
        async UniTask ShowArtworkFragmentAcquisitionView(
            ArtworkFragmentAcquisitionViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            
            var argument = new ArtworkFragmentAcquisitionViewController.Argument(
                viewModel,
                () =>
                {
                    completionSource.TrySetResult();
                });

            var viewController = ViewFactory.Create<
                ArtworkFragmentAcquisitionViewController,
                ArtworkFragmentAcquisitionViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
            
            await completionSource.Task;
        }
    }
}