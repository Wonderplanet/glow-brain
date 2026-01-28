using System;
using System.Collections.Generic;
using System.Runtime.InteropServices;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.Extension;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.View.MissionMain
{
    public interface IMissionMainControl
    {
        bool Interactable { get; }
        void SetInteractable(bool interactable);
        void CloseView();
        void DismissByChallenge();
        void SetBadgeVisible(MissionType type, bool visible);
        UniTask ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition,
            CancellationToken cancellationToken);
        void SetBulkReceiveAction(Action bulkReceiveAction);
        void SetBulkReceivable(bool isReceivable);
        void SetCloseButtonInteractable(bool interactable);
    }

    public class MissionMainViewController : UIViewController<MissionMainView>, IEscapeResponder ,IMissionMainControl
    {
        public record Argument(
            bool IsFirstLogin,
            bool IsDisplayFromItemDetailLocation,
            MissionType MissionType);
        [Inject] IMissionMainViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        bool IMissionMainControl.Interactable => ActualView.Interactable;

        Action BulkReceiveAction { get; set; }

        public Action OnCloseCompletion { get; set; }
        public Action OnDismissByChallenge {private get; set; }

        public UIViewController CurrentContentViewController
        {
            get;
            private set;
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void ShowCurrentContent(MissionType missionType, UIViewController viewController, bool worldPositionStays = true)
        {
            switch (missionType)
            {
                case MissionType.Achievement:
                    ShowAchievementMissionViewContent(viewController, false, worldPositionStays);
                    break;
                case MissionType.DailyBonus:
                    ShowDailyBonusMissionViewContent(viewController, false, worldPositionStays);
                    break;
                case MissionType.Daily:
                    ShowDailyMissionViewContent(viewController, false, worldPositionStays);
                    break;
                case MissionType.Weekly:
                    ShowWeeklyMissionViewContent(viewController, false, worldPositionStays);
                    break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(missionType), missionType, null);
            }

            ActualView.SetToggleOn(missionType);
            SetTitleText(missionType);
        }

        public void StartIndicator()
        {
            ActualView.StartLoading();
        }

        public void StopIndicator()
        {
            ActualView.StopLoading();
        }

        public void SetTitleText(MissionType missionType)
        {
            ActualView.SetTitleText(MissionTypeExtension.MissionTypeToMissionTypeName(missionType));
        }

        public void CloseView()
        {
            OnCloseCompletion?.Invoke();
            Dismiss();
        }

        void IMissionMainControl.SetInteractable(bool interactable)
        {
            ActualView.UserInteraction = interactable;
        }

        void IMissionMainControl.CloseView()
        {
            ActualView.Interactable = false;
            CloseView();
        }

        void IMissionMainControl.DismissByChallenge()
        {
            OnDismissByChallenge?.Invoke();
            Dismiss();
        }

        void IMissionMainControl.SetBadgeVisible(MissionType type, bool visible)
        {
            ActualView.SetBadgeVisible(type, visible);
        }

        async UniTask IMissionMainControl.ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition,
            CancellationToken cancellationToken)
        {
            ActualView.SetOnSelectRewardInWindow(OnSelectRewardInWindow);
            await ActualView.ShowRewardListWindow(viewModels, windowPosition, cancellationToken);
        }

        void IMissionMainControl.SetBulkReceiveAction(Action bulkReceiveAction)
        {
            BulkReceiveAction = bulkReceiveAction;
            ActualView.SetBulkReceiveButtonVisible(BulkReceiveAction != null);
        }

        public void SetBulkReceivable(bool isReceivable)
        {
            ActualView.SetBulkReceiveButtonInteractable(isReceivable);
        }
        
        public void SetCloseButtonInteractable(bool interactable)
        {
            ActualView.SetCloseButtonInteractable(interactable);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnEscape();
            return true;
        }

        void ShowAchievementMissionViewContent(UIViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            Show(viewController, animated);
            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
        }

        void ShowDailyMissionViewContent(UIViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            Show(viewController, animated);
            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
        }

        void ShowDailyBonusMissionViewContent(UIViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            Show(viewController, animated);
            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
        }

        void ShowWeeklyMissionViewContent(UIViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            Show(viewController, animated);
            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
        }

        void OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnSelectRewardInWindow(viewModel);
        }

        [UIAction]
        void OnDailyBonusMissionTabSelected()
        {
            ViewDelegate.OnDailyBonusMissionTabSelected();
        }

        [UIAction]
        void OnDailyMissionTabSelected()
        {
            ViewDelegate.OnDailyMissionTabSelected();
        }

        [UIAction]
        void OnWeeklyMissionTabSelected()
        {
            ViewDelegate.OnWeeklyMissionTabSelected();
        }

        [UIAction]
        void OnAchievementMissionTabSelected()
        {
            ViewDelegate.OnAchievementMissionTabSelected();
        }

        [UIAction]
        void OnBulkReceiveSelected()
        {
            BulkReceiveAction();
        }

        [UIAction]
        void OnCloseSelected()
        {
            CloseView();
        }
    }
}
