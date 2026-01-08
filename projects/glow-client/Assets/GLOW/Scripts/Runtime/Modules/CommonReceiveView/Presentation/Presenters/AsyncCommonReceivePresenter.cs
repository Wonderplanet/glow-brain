using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.Mission.Presentation.Presenter;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Presentation.Presenters
{
    public class AsyncCommonReceivePresenter : ICommonReceiveViewDelegate, IAsyncCommonReceiveViewControl
    {
        [Inject] AsyncCommonReceiveViewController ViewController { get; }
        [Inject] AsyncCommonReceiveViewController.Argument Argument { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }
        [Inject] UnreceivedRewardWireframe UnreceivedRewardWireframe { get; }

        bool _isListAnimationComplete;

        bool _canListAnimationSkip;

        CancellationToken AsyncReceiveCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void ICommonReceiveViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(AsyncCommonReceivePresenter), "OnViewDidLoad");

            // 閉じる文言を非表示にする
            ViewController.SetEnableCloseText(false);
        }

        void ICommonReceiveViewDelegate.OnViewWillAppear()
        {
            ApplicationLog.Log(nameof(AsyncCommonReceivePresenter), "OnViewWillAppear");

            // OnViewDidLoadで実行すると、Argument.DataSourceが（通信OFFとかで）即エラーになった場合に
            // エラーダイアログがこのAsyncCommonReceiveViewより先に表示されてしまうので、OnViewWillAppearで実行する
            DoAsync.Invoke(AsyncReceiveCancellationToken, async cancellationToken =>
            {
                // UseCaseをラムダで渡す or DataSourceとしてPlayerResourceIconViewModelを作るタスクを渡す
                // アニメーション制御とかは全部Controller側で
                var animationTask = UniTask.Create(async () =>
                {
                    await ViewController.PlayRewardLabelAnimation(cancellationToken);
                    ViewController.ShowLoading();
                }).AsAsyncUnitUniTask();

                var (_, viewModels) = await UniTask.WhenAll(animationTask, Argument.DataSource(AsyncReceiveCancellationToken));
                Argument.OnReceivedReward?.Invoke();

                var iconViewModels = viewModels
                    .Select(model =>
                    {
                        if (model.PreConversionPlayerResourceIconViewModel.IsEmpty())
                        {
                            return new PlayerResourceIconWithPreConversionViewModel(
                                model.PlayerResourceIconViewModel,
                                PlayerResourceIconViewModel.Empty);
                        }
                        return new PlayerResourceIconWithPreConversionViewModel(
                            model.PreConversionPlayerResourceIconViewModel,
                            model.PlayerResourceIconViewModel);
                    })
                    .ToList();

                ViewController.SetupScrollRectSize(iconViewModels);
                ViewController.StopLoadingAnimation();

                // キャラを獲得している場合は演出を表示する
                await ShowReceivedUnits(iconViewModels, cancellationToken);

                ViewController.ShowAcquiredPlayerResources(iconViewModels, () => { ViewController.ViewClosable(); });
                ViewController.UpdateLayout();

                ShowUnreceivedRewardReasonsIfNeeded(viewModels);
            });
        }

        void ShowUnreceivedRewardReasonsIfNeeded(IReadOnlyList<CommonReceiveResourceViewModel> resourceViewModels)
        {
            if (resourceViewModels.Count == 0) return;

            if (resourceViewModels
                .Exists(r =>r.UnreceivedRewardReasonType ==  UnreceivedRewardReasonType.SentToMessage))
            {
                UnreceivedRewardWireframe.ShowSentToMailbox();
            }

            if (resourceViewModels
                .Exists(r => r.UnreceivedRewardReasonType == UnreceivedRewardReasonType.ResourceLimitReached))
            {
                UnreceivedRewardWireframe.ShowResourceLimitReached();
            }

            if (resourceViewModels
                .Exists(r => r.UnreceivedRewardReasonType == UnreceivedRewardReasonType.ResourceOverflowDiscarded))
            {
                UnreceivedRewardWireframe.ShowResourceOverflowDiscarded();
            }
        }


        void IAsyncCommonReceiveViewControl.CanSkipRewardAnimation()
        {
            _canListAnimationSkip = true;
        }

        void IAsyncCommonReceiveViewControl.OnListAnimationCompleted()
        {
            ViewController.SetEnableCloseText(true);
            _isListAnimationComplete = true;
        }

        void ICommonReceiveViewDelegate.OnIconSelected(PlayerResourceIconViewModel viewModel)
        {
            if (!_isListAnimationComplete) return;

            switch (viewModel.ResourceType)
            {
                case ResourceType.Coin:
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                case ResourceType.Exp:
                case ResourceType.Item:
                case ResourceType.Emblem:
                case ResourceType.MissionBonusPoint:
                    ShowItemDetail(viewModel);
                    break;
                case ResourceType.ArtworkFragment:
                case ResourceType.Unit:
                case ResourceType.IdleCoin:
                    break;
            }
        }


        void ICommonReceiveViewDelegate.OnCloseSelected()
        {
            if (!_canListAnimationSkip)
                return;

            if (!_isListAnimationComplete)
            {
                ViewController.SkipAnimation();
            }
            else
            {
                ViewController.Dismiss(completion: Argument.OnViewClosed);
            }
        }

        void ShowItemDetail(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                viewModel.ResourceType,
                viewModel.Id,
                viewModel.Amount,
                ViewController);
        }

        async UniTask ShowReceivedUnits(
            IReadOnlyList<PlayerResourceIconWithPreConversionViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            var receivedUnitIds = viewModels
                .Where(model => model.PlayerResourceIcon.ResourceType == ResourceType.Unit
                    && model.ConvertedPlayerResourceIcon.IsEmpty())
                .Select(model => model.PlayerResourceIcon.Id)
                .ToList();

            await UnitReceiveWireFrame.ShowReceivedUnits(
                receivedUnitIds,
                ViewController,
                cancellationToken);
        }
    }
}
