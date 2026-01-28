using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.View.EventMissionMain
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-6_イベントミッション
    /// </summary>
    public interface IEventMissionMainControl
    {
        bool Interactable { get; }
        void SetInteractable(bool interactable);
        void SetIndicatorVisible(bool visible);
        void CloseView();
        void DismissByChallenge();
        void SetTabVisible(bool visible);
        void SetBadgeVisible(MissionType type, bool visible);
        void SetBulkReceiveButtonInteractable(bool interactable);
        void SetCloseButtonInteractable(bool interactable);
        void SetBulkReceiveAction(Action bulkReceiveAction);
        void SetBulkReceiveVisible(bool visible);
    }
    public class EventMissionMainViewController :
        UIViewController<EventMissionMainView>,
        IEscapeResponder,
        IEventMissionMainControl
    {
        public record Argument(bool DailyBonusAnimationPlaying, bool IsEventMissionOpenInHome, MasterDataId MstEventId)
        {
            //イベント画面からミッションを出すときは対象のイベントしか出さないのでMstEventIdが入ってくる
            public bool IsDisplayedInHome => MstEventId.IsEmpty();
        };

        [Inject] IEventMissionMainViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        bool IEventMissionMainControl.Interactable => ActualView.Interactable;

        public Action OnCloseCompletion { get; set; }
        public Action BulkReceiveAction { get; private set; }
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

        public void CloseView()
        {
            OnCloseCompletion?.Invoke();
            Dismiss();
        }

        void IEventMissionMainControl.SetInteractable(bool interactable)
        {
            ActualView.Interactable = interactable;
        }

        void IEventMissionMainControl.SetIndicatorVisible(bool visible)
        {
            ActualView.Indicator.Hidden = !visible;
        }

        void IEventMissionMainControl.CloseView()
        {
            ActualView.Interactable = false;
            CloseView();
        }

        void IEventMissionMainControl.DismissByChallenge()
        {
            OnDismissByChallenge?.Invoke();
            Dismiss();
        }

        void IEventMissionMainControl.SetTabVisible(bool visible)
        {
            ActualView.TabGroup.Hidden = !visible;
        }

        void IEventMissionMainControl.SetBadgeVisible(MissionType type, bool visible)
        {
            ActualView.SetBadgeVisible(type, visible);
        }

        void IEventMissionMainControl.SetBulkReceiveButtonInteractable(bool interactable)
        {
            ActualView.SetBulkReceiveButtonInteractable(interactable);
        }
        
        void IEventMissionMainControl.SetCloseButtonInteractable(bool interactable)
        {
            ActualView.SetCloseButtonInteractable(interactable);
        }

        void IEventMissionMainControl.SetBulkReceiveAction(Action bulkReceiveAction)
        {
            BulkReceiveAction = bulkReceiveAction;
        }

        void IEventMissionMainControl.SetBulkReceiveVisible(bool visible)
        {
            ActualView.SetBulkReceiveButtonVisible(visible);
        }

        public void ShowCurrentContent(MissionType missionType, UIViewController viewController,
            bool worldPositionStays)
        {
            // イベントミッションの中身の表示を形成する
            ShowEventMissionViewContent(viewController, false, worldPositionStays);

            ActualView.SetToggleOn(missionType);
            ActualView.SetTitle(missionType);
        }

        public void SetUpHeaderBannerImage(
            EventMissionCommonHeaderViewModel viewModel,
            bool isDailyBonus)
        {
            if (isDailyBonus)
            {
                ActualView.SetUpHeaderDailyBonusBannerImage(viewModel.DailyBonusBannerAssetPath, true);
                ActualView.SetUpHeaderMissionBannerImage(viewModel.MissionBannerAssetPath,false);
            }
            else
            {
                ActualView.SetUpHeaderMissionBannerImage(viewModel.MissionBannerAssetPath,true);
                ActualView.SetUpHeaderDailyBonusBannerImage(viewModel.DailyBonusBannerAssetPath, false);
            }
        }

        public void UpdateBannerVisible(bool isDailyBonus)
        {
            ActualView.UpdateBannerVisible(isDailyBonus);
        }

        public void SetUpHeaderRemainingTime(RemainingTimeSpan remainingTimeSpan)
        {
            ActualView.SetUpHeaderRemainingTime(remainingTimeSpan);
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

        void ShowEventMissionViewContent(UIViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
            AddChild(viewController);
            viewController.BeginAppearanceTransition(false, animated);
            viewController.EndAppearanceTransition();
        }

        [UIAction]
        void OnEventLoginBonusTabSelected()
        {
            ViewDelegate.OnEventDailyBonusTabSelected();
        }

        [UIAction]
        void OnEventAchievementTabSelected()
        {
            ViewDelegate.OnEventAchievementTabSelected();
        }

        [UIAction]
        void OnBulkReceiveButtonSelected()
        {
            ViewDelegate.OnBulkReceiveButtonSelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }

    }
}
