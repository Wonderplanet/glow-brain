using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Modules.CommonReceiveView.Presentation.Presenters
{
    public class CommonReceiveViewPresenter : ICommonReceiveViewDelegate
    {
        [Inject] CommonReceiveViewController ViewController { get; }
        [Inject] CommonReceiveViewController.Argument Argument { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }
        [Inject] UnreceivedRewardWireframe UnreceivedRewardWireframe { get; }

        bool _isListAnimationCanSkip;
        bool _isListAnimationComplete;

        void ICommonReceiveViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(CommonReceiveViewPresenter), nameof(ICommonReceiveViewDelegate.OnViewDidLoad));

            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                if (!Argument.RewardTitle.IsDefault())
                {
                    ViewController.SetRewardTitleText(Argument.RewardTitle);
                }
                if (!Argument.ReceivedRewardDescription.IsEmpty())
                {
                    ViewController.SetRewardDescriptionText(Argument.ReceivedRewardDescription);
                }

                var iconViewModels = Argument.CommonReceiveResourceViewModels
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
                await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken: cancellationToken);

                ViewController.FadeInDescriptionText(cancellationToken).Forget();

                // キャラを獲得している場合は演出を表示する
                await ShowReceivedUnits(iconViewModels, cancellationToken);

                ViewController.ShowAcquiredPlayerResources(iconViewModels, OnListAnimationCompleted);

                _isListAnimationCanSkip = true;

                ShowUnreceivedRewardReasonsIfNeeded();
            });
        }
        void ShowUnreceivedRewardReasonsIfNeeded()
        {
            if (Argument.CommonReceiveResourceViewModels.Count == 0) return;

            if (Argument.CommonReceiveResourceViewModels
                .Exists(r =>r.UnreceivedRewardReasonType ==  UnreceivedRewardReasonType.SentToMessage))
            {
                UnreceivedRewardWireframe.ShowSentToMailbox();
            }

            if (Argument.CommonReceiveResourceViewModels
                .Exists(r =>r.UnreceivedRewardReasonType == UnreceivedRewardReasonType.ResourceLimitReached))
            {
                UnreceivedRewardWireframe.ShowResourceLimitReached();
            }

            if (Argument.CommonReceiveResourceViewModels
                .Exists(r => r.UnreceivedRewardReasonType == UnreceivedRewardReasonType.ResourceOverflowDiscarded))
            {
                UnreceivedRewardWireframe.ShowResourceOverflowDiscarded();
            }
        }

        void ICommonReceiveViewDelegate.OnViewWillAppear()
        {
            // no use.
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
                case ResourceType.Unit:
                case ResourceType.Artwork:
                case ResourceType.Emblem:
                    ShowItemDetail(viewModel);
                    break;
                case ResourceType.ArtworkFragment:
                case ResourceType.IdleCoin:
                case ResourceType.MissionBonusPoint:
                    break;
            }
        }

        void ICommonReceiveViewDelegate.OnCloseSelected()
        {
            if (!_isListAnimationCanSkip)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            if (!_isListAnimationComplete)
            {
                ViewController.SkipAnimation();
                return;
            }

            ViewController.Dismiss(completion: ViewController.OnViewClosed);
        }

        void ShowItemDetail(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void OnListAnimationCompleted()
        {
            _isListAnimationComplete = true;
            ViewController.ShowCloseText();
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
