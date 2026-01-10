using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.ComeBackDailyBonus.Domain.UseCase;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.Factory;
using GLOW.Scenes.ComebackDailyBonus.Presentation.View;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.ComebackDailyBonus.Presentation.Presenter
{
    public class ComebackDailyBonusPresenter : IComebackDailyBonusViewDelegate
    {
        [Inject] ComebackDailyBonusViewController ViewController { get; }
        [Inject] ComebackDailyBonusViewController.Argument Argument { get; }
        [Inject] ShowComebackDailyBonusUseCase ShowComebackDailyBonusUseCase { get; }
        [Inject] IComebackDailyBonusViewModelFactory ComebackDailyBonusViewModelFactory { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        
        CancellationToken ComebackDailyBonusMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        void IComebackDailyBonusViewDelegate.OnViewDidLoad()
        {
            var model = ShowComebackDailyBonusUseCase.UpdateAndFetchComebackDailyBonusModel(Argument.MstComebackDailyBonusScheduleId);
            var viewModel = ComebackDailyBonusViewModelFactory.Create(model);
            
            ViewController.SetUpComebackDailyBonusView(viewModel);
            
            DoAsync.Invoke(ComebackDailyBonusMissionCancellationToken, async cancellationToken =>
            {
                await UniTask.Delay(10, cancellationToken: cancellationToken);
                await PlayComebackDailyBonusAnimation(viewModel, cancellationToken);
            });
        }

        void IComebackDailyBonusViewDelegate.OnCloseButtonSelected()
        {
            ViewController.OnCloseCompletion?.Invoke();
            ViewController.Dismiss();
        }

        void IComebackDailyBonusViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            OnIconSelected(playerResourceIconViewModel);
        }
        
        async UniTask PlayComebackDailyBonusAnimation(
            ComebackDailyBonusViewModel viewModel, 
            CancellationToken cancellationToken)
        {
            var animationPlayDayCell = viewModel.GetReceivingAnimationPlayDayCell();

            // 報酬がない場合はそのまま
            if (animationPlayDayCell.IsEmpty()) return;
            
            // 閉じるボタンを押せないようにする
            ViewController.SetCloseButtonInteractable(false);
            
            // アニメーションを再生
            await UniTask.Delay(500, cancellationToken: cancellationToken);
            await ViewController.PlayAnimation(animationPlayDayCell.LoginDayCount, cancellationToken);
            
            HomeHeaderDelegate.UpdateStatus();
            
            // 受け取った報酬を表示
            await ShowCommonReceive(viewModel.CommonReceiveResourceModels, cancellationToken);
            
            // 閉じるボタンを押せるようにする
            ViewController.SetCloseButtonInteractable(true);
            
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
                    ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
                    break;
                case ResourceType.MissionBonusPoint:
                case ResourceType.ArtworkFragment:
                case ResourceType.IdleCoin:
                case ResourceType.Emblem:
                    break;
            }
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