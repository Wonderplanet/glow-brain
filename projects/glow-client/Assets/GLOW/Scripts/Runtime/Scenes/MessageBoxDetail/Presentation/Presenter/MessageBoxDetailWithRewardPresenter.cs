using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.MessageBox.Presentation.Presenter;
using GLOW.Scenes.MessageBox.Presentation.Translator;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel;
using GLOW.Scenes.Shop.Domain.Calculator;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Presenter
{
    public class MessageBoxDetailWithRewardPresenter : IMessageBoxDetailWithRewardViewDelegate
    {
        [Inject] MessageBoxDetailWithRewardViewController ViewController { get; }
        [Inject] MessageBoxDetailWithRewardViewController.Argument Argument { get; }
        [Inject] ReceiveMessageRewardUseCase ReceiveMessageRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] CanReceiveMessageUseCase CanReceiveMessageUseCase { get; }
        [Inject] IUnreceivedMessageWireframe UnreceivedMessageWireframe { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        void IMessageBoxDetailWithRewardViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailWithRewardPresenter), "OnViewDidLoad");

            ViewController.SetViewModel(Argument.ViewModel);
        }

        void IMessageBoxDetailWithRewardViewDelegate.OnClose()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailWithRewardPresenter), "OnClose");

            ViewController.Dismiss();
        }

        void IMessageBoxDetailWithRewardViewDelegate.OnReceiveRewardSelected()
        {
            if (HasLimitAmountItem(Argument.ViewModel))
            {
                LimitAmountWireframe.ShowItemReceiveLimitView();
                return;
            }

            var canReceive = CanReceiveMessageUseCase.IsReceivable(new List<MasterDataId>()
            {
                Argument.ViewModel.MessageId
            });

            if (!canReceive)
            {
                UnreceivedMessageWireframe.ShowUnreceivedExpiredMessageView(() =>
                {
                    ViewController.OnReceiveExpired?.Invoke();
                    ViewController.Dismiss();
                });
                return;
            }

            ReceiveReward();
        }

        void IMessageBoxDetailWithRewardViewDelegate.OnRewardSelected(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void ReceiveReward()
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                CreateReceiveMessageRewardFunc,
                () => ViewController.Dismiss()
            );
        }

        bool HasLimitAmountItem(MessageBoxDetailViewModel viewModel)
        {
            var limitAmountModel =
                viewModel.RewardList
                    .Select(r => new LimitCheckModel(r.Id, r.ResourceType, r.Amount.Value))
                    .ToList();
            return LimitAmountModelCalculator.FilteringLimitAmount(limitAmountModel).Any();
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> CreateReceiveMessageRewardFunc(CancellationToken cancellationToken)
        {
            return UniTask.Create(async () =>
            {
                try
                {
                    // 受取処理
                    var model = await ReceiveMessageRewardUseCase.ReceiveMessageReward(
                        cancellationToken,
                        new List<MasterDataId> { Argument.ViewModel.MessageId });

                    ViewController.OnReceiveCompleted?.Invoke(
                        MessageBoxViewModelTranslator.ToMessageBoxViewModel(model.CommonResultUseCaseModel.UpdatedList),
                        model.CommonResultUseCaseModel.CanBulkReceive,
                        model.CommonResultUseCaseModel.CanBulkOpen);

                    // ホームヘッダーの更新
                    HomeHeaderDelegate.UpdateStatus();

                    var rewardViewModels = model.ReceivedRewards
                        .Select(r => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                        .ToList();

                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(rewardViewModels);
                }
                catch (ExpiredMessageResourceException e)
                {
                    ViewController.OnReceiveExpired?.Invoke();
                    UnreceivedMessageWireframe.ShowUnreceivedExpiredMessageView(() =>
                    {
                        CommonReceiveWireFrame.DismissDisplayedAsyncCommonReceive();
                        ViewController.Dismiss();
                    });
                    return new List<CommonReceiveResourceViewModel>();
                }
                catch (MessageRewardByOverMaxException e)
                {
                    UnreceivedMessageWireframe.ShowUnreceivedLimitAmountMessageView(() =>
                    {
                        CommonReceiveWireFrame.DismissDisplayedAsyncCommonReceive();
                        ViewController.Dismiss();
                    });
                    return new List<CommonReceiveResourceViewModel>();
                }
            });
        }
    }
}
