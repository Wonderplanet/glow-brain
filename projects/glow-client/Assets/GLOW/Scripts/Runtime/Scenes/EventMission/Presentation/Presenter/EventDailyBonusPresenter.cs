using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.EventMission.Presentation.Tranalator;
using GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using ModestTree;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.Presenter
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-3_ログインボーナス
    /// </summary>
    public class EventDailyBonusPresenter : IEventDailyBonusViewDelegate
    {
        [Inject] EventDailyBonusViewController ViewController { get; }
        [Inject] EventDailyBonusViewController.Argument Argument { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IEventMissionMainControl EventMissionMainViewControl { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] UpdatedReceivingEventDailyBonusUseCase UpdatedReceivingEventDailyBonusUseCase { get; }

        CancellationToken EventDailyBonusMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        

        public void OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            EventMissionMainViewControl.SetBulkReceiveButtonInteractable(false);
            EventMissionMainViewControl.SetBulkReceiveAction(null);
            EventMissionMainViewControl.SetBulkReceiveVisible(false);

            DoAsync.Invoke(EventDailyBonusMissionCancellationToken, ViewController, async cancellationToken =>
            {
                var animationPlayDayCell = Argument.ViewModel.GetReceivingAnimationPlayDayCell();
                
                if (Argument.ViewModel.ReceiveResourceRewardViewModels.IsEmpty()) return;
                if (animationPlayDayCell.IsEmpty()) return;
                
                EventMissionMainViewControl.SetInteractable(false);
                
                await UniTask.Delay(10, cancellationToken: cancellationToken);
                await TryPlayEventDailyBonusAnimation(cancellationToken, animationPlayDayCell.LoginDayCount);
                
                EventMissionMainViewControl.SetInteractable(true);
                EventMissionMainViewControl.SetCloseButtonInteractable(true);

                // 受け取り済みの状態を反映する
                var updatedEventDailyBonusResultModel = UpdatedReceivingEventDailyBonusUseCase.UpdateReceivingEventDailyBonus(
                    Argument.ViewModel.MstEventIdForTimeInformation);
                var viewModel = EventMissionViewModelTranslator.ToEventDailyBonusViewModel(
                        Argument.ViewModel.MstEventIdForTimeInformation,
                        updatedEventDailyBonusResultModel);
                ViewController.OnReceivedAction?.Invoke(viewModel);
            });
        }

        public void OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            OnIconSelected(playerResourceIconViewModel);
        }

        public void OnEscape()
        {
            if (!EventMissionMainViewControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            EventMissionMainViewControl.CloseView();
        }

        async UniTask TryPlayEventDailyBonusAnimation(
            CancellationToken cancellationToken,
            LoginDayCount loginDayCount)
        {
            EventMissionMainViewControl.SetCloseButtonInteractable(false);
            
            await UniTask.Delay(500, cancellationToken: cancellationToken);

            await ViewController.PlayAnimation(loginDayCount, cancellationToken);
            
            HomeHeaderDelegate.UpdateStatus();

            await ShowCommonReceive(Argument.ViewModel.ReceiveResourceRewardViewModels, cancellationToken);
        }

        void OnIconSelected(PlayerResourceIconViewModel viewModel)
        {
            switch (viewModel.ResourceType)
            {
                case ResourceType.Coin:
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                case ResourceType.Exp:
                case ResourceType.Item:
                case ResourceType.Unit:
                    ShowItemDetail(viewModel);
                    break;
                case ResourceType.MissionBonusPoint:
                case ResourceType.ArtworkFragment:
                case ResourceType.IdleCoin:
                case ResourceType.Emblem:
                    break;
            }
        }

        void ShowItemDetail(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        async UniTask ShowCommonReceive(
            IReadOnlyList<CommonReceiveResourceViewModel> resourceViewModels,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var onCloseCompletion = new Action(() => { completionSource.TrySetResult(); });

            CommonReceiveWireFrame.Show(resourceViewModels, onClosed: onCloseCompletion);
            await completionSource.Task;
        }
    }
}
