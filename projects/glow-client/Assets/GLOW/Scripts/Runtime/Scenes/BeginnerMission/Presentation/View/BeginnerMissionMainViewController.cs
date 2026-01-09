using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public interface IBeginnerMissionMainControl
    {
        bool Interactable { get; }
        void SetInteractable(bool interactable);
        void SetIndicatorVisible(bool visible);
        void CloseView();
        void DismissByChallenge(Action completion);
        void SetBonusPointViewModel(IBonusPointMissionViewModel viewModel);
        void SetReceivableTotalDiamondAmount(BeginnerMissionPromptPhrase promptPhrase);
        void SetBadgeVisible(BeginnerMissionDayNumber number, bool visible);
        void SetMissionComponentVisible(bool visible);
        UniTask ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition,
            CancellationToken cancellationToken);
        void SetBulkReceivable(bool isReceivable);
        void PlayReceiveAnimationAfterReceivedPoints();
        UniTask OpenUnlockDayAnimation(
            BeginnerMissionDaysFromStart prev,
            BeginnerMissionDaysFromStart current,
            CancellationToken cancellationToken);
    }

    public class BeginnerMissionMainViewController :
        UIViewController<BeginnerMissionMainView>,
        IEscapeResponder,
        IBeginnerMissionMainControl
    {
        [Inject] IBeginnerMissionMainViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        bool IBeginnerMissionMainControl.Interactable => ActualView.Interactable;
        public Action OnCloseCompletion { get; set; }

        IBonusPointMissionViewModel _viewModel;
        public BeginnerMissionContentViewController CurrentContentViewController
        {
            get;
            private set;
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public void ShowCurrentContent(
            BeginnerMissionDayNumber dayNumber,
            BeginnerMissionContentViewController viewController,
            bool worldPositionStays = true)
        {
            ShowBeginnerMissionContent(viewController, false, worldPositionStays);
            ActualView.SetToggleOn(dayNumber);
        }

        public void SetUpLockIconVisible(BeginnerMissionDaysFromStart daysFromStart)
        {
            ActualView.SetUpLockIconVisible(daysFromStart);
        }

        public async UniTask OpenRewardBoxAnimation(
            CancellationToken cancellationToken,
            BonusPoint bonusPoint)
        {
            await ActualView.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }

        public void SetupBonusPointGaugeRate(
            BonusPoint currentBonusPoint,
            BonusPoint maxBonusPoint)
        {
            ActualView.BonusPointComponent.SetBonusPointNumber(currentBonusPoint);
            ActualView.BonusPointComponent.SetProgressGaugeRate(
                currentBonusPoint.ToGaugeRate(maxBonusPoint));
        }

        public async UniTask PlayBonusPointGaugeAnimation(CancellationToken cancellationToken,
            BonusPoint updatedBonusPoint,
            BonusPoint maxBonusPoint)
        {
            ActualView.BonusPointComponent.SetBonusPointNumber(updatedBonusPoint);
            await ActualView.BonusPointComponent.PlayProgressGaugeAnimation(
                cancellationToken,
                updatedBonusPoint,
                maxBonusPoint);
        }

        public void UpdateBonusPointComponent()
        {
            ActualView.BonusPointComponent.Setup(_viewModel, ShowRewardListWindow);
        }

        public void CloseView()
        {
            OnCloseCompletion?.Invoke();
            Dismiss();
        }

        void IBeginnerMissionMainControl.SetInteractable(bool interactable)
        {
            ActualView.Interactable = interactable;
        }

        void IBeginnerMissionMainControl.SetIndicatorVisible(bool visible)
        {
            ActualView.Indicator.Hidden = !visible;
        }

        void IBeginnerMissionMainControl.CloseView()
        {
            ActualView.Interactable = false;
            CloseView();
        }

        void IBeginnerMissionMainControl.DismissByChallenge(Action completion)
        {
            Dismiss(completion: completion);
        }

        void IBeginnerMissionMainControl.SetBonusPointViewModel(IBonusPointMissionViewModel viewModel)
        {
            _viewModel = viewModel;
        }

        void IBeginnerMissionMainControl.SetReceivableTotalDiamondAmount(BeginnerMissionPromptPhrase promptPhrase)
        {
            ActualView.SetReceivableTotalDiamondAmount(promptPhrase);
        }

        void IBeginnerMissionMainControl.SetBadgeVisible(BeginnerMissionDayNumber number, bool visible)
        {
            ActualView.SetBadgeVisible(number, visible);
        }

        void IBeginnerMissionMainControl.SetMissionComponentVisible(bool visible)
        {
            ActualView.SetMissionComponentVisible(visible);
        }

        UniTask IBeginnerMissionMainControl.ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition,
            CancellationToken cancellationToken)
        {
            ActualView.SetOnSelectRewardInWindow(OnSelectRewardInWindow);
            return ActualView.ShowRewardListWindow(viewModels, windowPosition, cancellationToken);
        }

        void IBeginnerMissionMainControl.SetBulkReceivable(bool isReceivable)
        {
            ActualView.SetBulkReceiveButtonInteractable(isReceivable);
        }

        void IBeginnerMissionMainControl.PlayReceiveAnimationAfterReceivedPoints()
        {
            ViewDelegate.PlayReceiveAnimationAfterReceivedPoints();
        }

        async UniTask IBeginnerMissionMainControl.OpenUnlockDayAnimation(
            BeginnerMissionDaysFromStart prev,
            BeginnerMissionDaysFromStart current,
            CancellationToken cancellationToken)
        {
            var unlockAnimationList = new List<UniTask>();
            for (var i = prev.Value + 1; i <= current.Value; i++)
            {
                // 宝箱のアニメーションが全部終わるのを待つために変数を入れる
                float delayTime = (i - (prev.Value + 1)) * 0.2f;
                unlockAnimationList.Add(ActualView.PlayUnlockDayAnimation(i, delayTime, cancellationToken));
            }

            // 宝箱のアニメーションが全部終わるまで待つ
            await UniTask.WhenAll(unlockAnimationList);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
                return false;

            ViewDelegate.OnEscape();
            return true;
        }

        void ShowBeginnerMissionContent(
            BeginnerMissionContentViewController viewController,
            bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
            AddChild(viewController);
            viewController.BeginAppearanceTransition(false, animated);
            viewController.EndAppearanceTransition();
        }

        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition)
        {
            ViewDelegate.ShowRewardListWindow(viewModels, windowPosition);
        }

        void OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnSelectRewardInWindow(viewModel);
        }

        [UIAction]
        void OnDayOneTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(1));
        }

        [UIAction]
        void OnDayTwoTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(2));
        }

        [UIAction]
        void OnDayThreeTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(3));
        }

        [UIAction]
        void OnDayFourTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(4));
        }

        [UIAction]
        void OnDayFiveTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(5));
        }

        [UIAction]
        void OnDaySixTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(6));
        }

        [UIAction]
        void OnDaySevenTabSelected()
        {
            ViewDelegate.OnDayNumberTabSelected(new BeginnerMissionDayNumber(7));
        }

        [UIAction]
        void OnBulkReceiveSelected()
        {
            ViewDelegate.BulkReceive();
        }

        [UIAction]
        void OnCloseSelected()
        {
            CloseView();
        }
    }
}
