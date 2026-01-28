using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.MessageBox.Presentation.Translator;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.MessageBoxDetail.Presentation.Control;
using GLOW.Scenes.MessageBoxDetail.Presentation.Transtator;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using GLOW.Scenes.Shop.Domain.Calculator;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.MessageBox.Presentation.Presenter
{
    public class MessageBoxPresenter : IMessageBoxViewDelegate
    {
        [Inject] MessageBoxViewController ViewController { get; }
        [Inject] GetMessageListUseCase GetMessageListUseCase { get; }
        [Inject] BulkOpenMessageUseCase BulkOpenMessageUseCase { get; }
        [Inject] ReceiveMessageRewardUseCase ReceiveMessageRewardUseCase { get; }
        [Inject] IMessageBoxDetailViewControl MessageBoxDetailViewControl { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] CanReceiveMessageUseCase CanReceiveMessageUseCase { get; }
        [Inject] OpenMessageUseCase OpenMessageUseCase { get; }
        [Inject] IUnreceivedMessageWireframe UnreceivedMessageWireframe { get; }

        IReadOnlyList<MasterDataId> _messageIds;
        CancellationToken MessageBoxCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(MessageBoxPresenter), nameof(OnViewDidLoad));

            FetchMessageList();
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(MessageBoxPresenter), nameof(OnViewDidUnload));
        }

        void IMessageBoxViewDelegate.OnClose()
        {
            ViewController.Dismiss(completion: ViewController.OnCloseAction);
        }

        void IMessageBoxViewDelegate.OnMessageSelected(IMessageBoxCellViewModel viewModel, UIIndexPath indexPath)
        {
            UpdateOpenedMessageList(viewModel, indexPath);

            var contentViewModel = MessageBoxDetailViewModelTranslator.ToMessageBoxDetailViewModel(viewModel);
            MessageBoxDetailViewControl.ShowMessageBoxContentView(
                ViewController,
                contentViewModel,
                ViewController.SetViewModel,
                FetchMessageList);
        }

        void IMessageBoxViewDelegate.OnBulkOpen(IReadOnlyList<IMessageBoxCellViewModel> cellViewModels)
        {
            // 所持上限チェック
            var checkModels = cellViewModels
                .SelectMany(m =>
                {
                    return m.MessageRewards
                        .Select(r => new LimitCheckModel(r.Id, r.ResourceType, r.Amount.Value))
                        .ToList();
                })
                .ToList();

            var limitItems = LimitAmountModelCalculator.FilteringLimitAmount(checkModels);
            if (limitItems.Any())
            {
                LimitAmountWireframe.ShowItemReceiveLimitView();
                return;
            }

            var receiveMessageIds = cellViewModels.Select(model => model.MessageId).ToList();
            var canAllReceive = CanReceiveMessageUseCase.IsReceivable(receiveMessageIds);
            if (!canAllReceive)
            {
                UnreceivedMessageWireframe.ShowUnopenedExpiredMessageView(FetchMessageList);
                return;
            }

            // 一括受取実行
            DoAsync.Invoke(MessageBoxCancellationToken, async cancellationToken =>
            {
                try
                {
                    var result = await BulkOpenMessageUseCase.OpenAndUpdateMessages(
                        cancellationToken,
                        cellViewModels.Select(m => m.MessageId).ToList());
                    var viewModel = MessageBoxViewModelTranslator.ToMessageBoxViewModel(result.UpdatedList);
                    ViewController.SetViewModel(viewModel, result.CanBulkReceive, result.CanBulkOpen);
                }
                catch (ExpiredMessageResourceException e)
                {
                    // エラーの場合はリストも更新する(既読は受け取りは発生しない)
                    UnreceivedMessageWireframe.ShowUnopenedExpiredMessageView(FetchMessageList);
                }
            });
        }

        void IMessageBoxViewDelegate.OnBulkReceive(IReadOnlyList<IMessageBoxCellViewModel> viewModels)
        {
            if (HasLimitAmountItem(viewModels))
            {
                // 上限チェックに引っかかった場合は、受け取れないダイアログを表示する
                LimitAmountWireframe.ShowItemReceiveLimitView();
                return;
            }

            var receiveMessageIds = viewModels.Select(model => model.MessageId).ToList();
            var canAllReceive = CanReceiveMessageUseCase.IsReceivable(receiveMessageIds);
            if (!canAllReceive)
            {
                UnreceivedMessageWireframe.ShowUnreceivedExpiredMessageView(FetchMessageList);
                return;
            }

            //受取実行
            _messageIds = viewModels.Select(vm => vm.MessageId).ToList();
            CommonReceiveWireFrame.AsyncShowReceived(
                    CreateReceiveMessageRewardFunc, () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
        }

        bool HasLimitAmountItem(IReadOnlyList<IMessageBoxCellViewModel> viewModels)
        {
            var limitAmountModel = viewModels.SelectMany(vm =>
                {
                    return vm.MessageRewards
                        .Select(r => new LimitCheckModel(r.Id, r.ResourceType, r.Amount.Value));
                })
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
                    var model =
                        await ReceiveMessageRewardUseCase.ReceiveMessageReward(cancellationToken, _messageIds);

                    var viewModel = MessageBoxViewModelTranslator.ToMessageBoxViewModel(
                        model.CommonResultUseCaseModel.UpdatedList);

                    // 画面更新
                    ViewController.SetViewModel(
                        viewModel,
                        model.CommonResultUseCaseModel.CanBulkReceive,
                        model.CommonResultUseCaseModel.CanBulkOpen);

                    //ヘッダー更新
                    HomeHeaderDelegate.UpdateStatus();

                    var viewModels = model.ReceivedRewards
                        .Select(r => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                }
                catch (ExpiredMessageResourceException e)
                {
                    UnreceivedMessageWireframe.ShowUnreceivedExpiredMessageView(() =>
                    {
                        // 受け取れないメールだった場合はエラーダイアログを閉じると同時に報酬受け取りも閉じる
                        CommonReceiveWireFrame.DismissDisplayedAsyncCommonReceive();
                        FetchMessageList();
                    });
                    return new List<CommonReceiveResourceViewModel>();
                }
                catch (MessageRewardByOverMaxException e)
                {
                    UnreceivedMessageWireframe.ShowUnreceivedLimitAmountMessageView(() =>
                    {
                        // 受け取れないメールだった場合はエラーダイアログを閉じると同時に報酬受け取りも閉じる
                        CommonReceiveWireFrame.DismissDisplayedAsyncCommonReceive();
                    });
                    return new List<CommonReceiveResourceViewModel>();
                }
            });
        }

        void FetchMessageList()
        {
            // 通信前にoffにして動かないようにする
            ViewController.SetBulkButtonInteractable(false, false);

            DoAsync.Invoke(MessageBoxCancellationToken, async cancellationToken =>
            {
                var result = await GetMessageListUseCase.GetMessageList(cancellationToken);
                var viewModel = MessageBoxViewModelTranslator.ToMessageBoxViewModel(result.UpdatedList);
                ViewController.SetViewModel(viewModel, result.CanBulkReceive, result.CanBulkOpen);
                ViewController.PlayCellAppearanceAnimation();
            });
        }

        void UpdateOpenedMessageList(IMessageBoxCellViewModel viewModel, UIIndexPath indexPath)
        {
            if (viewModel.MessageStatus != MessageStatus.New) return;

            var updatedModel = OpenMessageUseCase.OpenAndUpdateSingleMessage(viewModel.MessageId);
            var updatedViewModel = MessageBoxViewModelTranslator.ToMessageBoxViewModel(updatedModel.UpdatedList);
            ViewController.UpdateViewModel(
                updatedViewModel,
                indexPath,
                updatedModel.CanBulkReceive,
                updatedModel.CanBulkOpen);
        }
    }
}
