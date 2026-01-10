using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Presenters;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.MessageBox.Presentation.Presenter;
using GLOW.Scenes.MessageBox.Presentation.Translator;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using GLOW.Scenes.Shop.Domain.Calculator;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.Presenter
{
    public class MessageBoxDetailPresenter : IMessageBoxDetailViewDelegate
    {
        [Inject] MessageBoxDetailViewController  ViewController { get; }
        [Inject] MessageBoxDetailViewController.Argument Argument { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] CanReceiveMessageUseCase CanReceiveMessageUseCase { get; }
        [Inject] IUnreceivedMessageWireframe UnreceivedMessageWireframe { get; }

        [Inject] BulkOpenMessageUseCase BulkOpenMessageUseCase { get; }

        CancellationToken MessageBoxDetailCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailPresenter), nameof(OnViewDidLoad));

            ViewController.SetViewModel(Argument.ViewModel);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(MessageBoxDetailPresenter), nameof(OnViewDidUnload));
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }

        public void OnOpenSelected()
        {
            // 所持上限チェック
            var checkModels = Argument.ViewModel.RewardList
                .Select(r => new LimitCheckModel(r.Id, r.ResourceType, r.Amount.Value))
                .ToList();
            var limitItems = LimitAmountModelCalculator.FilteringLimitAmount(checkModels);
            if (limitItems.Any())
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
                UnreceivedMessageWireframe.ShowUnopenedExpiredMessageView(() => ViewController.Dismiss());
                return;
            }

            // 受取処理実行
            DoAsync.Invoke(MessageBoxDetailCancellationToken, async cancellationToken =>
            {
                try
                {
                    var result = await BulkOpenMessageUseCase.OpenAndUpdateMessages(cancellationToken,
                        new List<MasterDataId>{ Argument.ViewModel.MessageId });
                    var viewModel =
                        MessageBoxViewModelTranslator.ToMessageBoxViewModel(result.UpdatedList);
                    ViewController.OnOpenCompleted?.Invoke(viewModel, result.CanBulkReceive, result.CanBulkOpen);
                    ViewController.Dismiss();
                }
                catch (ExpiredMessageResourceException e)
                {
                    UnreceivedMessageWireframe.ShowUnopenedExpiredMessageView(() =>
                    {
                        ViewController.OnOpenExpired?.Invoke();
                        ViewController.Dismiss();
                    });
                }
            });
        }
    }
}
