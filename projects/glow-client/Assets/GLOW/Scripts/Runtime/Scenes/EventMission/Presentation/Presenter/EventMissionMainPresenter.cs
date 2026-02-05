using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.EventMission.Presentation.Tranalator;
using GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventDailyBonus;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain;
using UIKit;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.Presenter
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-6_イベントミッション
    /// </summary>
    public class EventMissionMainPresenter : IEventMissionMainViewDelegate
    {
        [Inject] IEventMissionMainControl EventMissionMainControl { get; }
        [Inject] EventMissionMainViewController ViewController { get; }
        [Inject] EventMissionMainViewController.Argument Argument { get; }
        [Inject] FetchEventMissionUseCase FetchEventMissionUseCase { get; }
        [Inject] FetchEventMissionCommonHeaderUseCase FetchEventMissionCommonHeaderUseCase { get; }
        [Inject] GetEventMissionTimeInformationUseCase GetEventMissionTimeInformationUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        CancellationToken EventMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        MissionType _currentMissionType;

        EventMissionMainViewModel _missionMainViewModel;
        EventMissionCommonHeaderViewModel _eventMissionCommonHeaderViewModel;
        
        CancellationTokenSource _updateNextUpdateTimeCancellationTokenSource = new();

        public void OnViewDidLoad()
        {
            var eventMissionCommonHeaderModel =
                FetchEventMissionCommonHeaderUseCase.GetEventMissionCommonHeader(Argument.MstEventId, Argument.IsDisplayedInHome);
            _eventMissionCommonHeaderViewModel =
                EventMissionCommonHeaderViewModelTranslator.ToEventMissionCommonHeaderViewModel(eventMissionCommonHeaderModel);

            var currentMissionTab = GetDisplayTab();

            SetUpEventBanner();
            UpdateNextUpdateTimeText(currentMissionTab);

            if (Argument.DailyBonusAnimationPlaying)
            {
                EventMissionMainControl.SetInteractable(false);
                EventMissionMainControl.SetCloseButtonInteractable(false);
                EventMissionMainControl.SetBulkReceiveAction(null);
                EventMissionMainControl.SetBulkReceiveVisible(false);
            }
            else
            {
                EventMissionMainControl.SetBulkReceiveButtonInteractable(false);
            }
            
            EventMissionMainControl.SetIndicatorVisible(true);
            EventMissionMainControl.SetTabVisible(false);

            DoAsync.Invoke(EventMissionCancellationToken, async cancellationToken =>
            {
                _missionMainViewModel = await FetchEventMissionList(cancellationToken);
                ShowCurrentContent(currentMissionTab);
                UpdateMissionNextUpdateTime();
                SetBadgeVisible();
                EventMissionMainControl.SetTabVisible(!_missionMainViewModel.EventDailyBonusViewModel.IsEmpty());
                EventMissionMainControl.SetIndicatorVisible(false);
            });
        }

        public void OnViewDidUnload()
        {
            _updateNextUpdateTimeCancellationTokenSource?.Cancel();
            _updateNextUpdateTimeCancellationTokenSource?.Dispose();
            _updateNextUpdateTimeCancellationTokenSource = null;
        }

        public void OnEventDailyBonusTabSelected()
        {
            SwitchEventMissionContent(MissionType.EventDailyBonus);
            ViewController.UpdateBannerVisible(true);
        }

        public void OnEventAchievementTabSelected()
        {
            SwitchEventMissionContent(MissionType.Event);
            ViewController.UpdateBannerVisible(false);
        }

        public void OnBulkReceiveButtonSelected()
        {
            ViewController.BulkReceiveAction?.Invoke();
        }

        public void OnCloseSelected()
        {
            ViewController.CloseView();
        }

        public void OnEscape()
        {
            if (!EventMissionMainControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            ViewController.CloseView();
        }

        async UniTask<EventMissionMainViewModel> FetchEventMissionList(CancellationToken cancellationToken)
        {
            var model = await FetchEventMissionUseCase.UpdateAndFetchEventMissionList(
                Argument.MstEventId,
                Argument.IsDisplayedInHome,
                cancellationToken);
            return EventMissionViewModelTranslator.ToEventMissionMainViewModel(model);
        }

        void SetBadgeVisible()
        {
            EventMissionMainControl.SetBadgeVisible(
                MissionType.Event,
                _missionMainViewModel.EventAchievementMissionViewModel.IsReceivableRewardExist());
        }

        void SwitchEventMissionContent(MissionType missionType)
        {
            if (_currentMissionType == missionType)
                return;

            RemoveCurrentContent();
            ShowCurrentContent(missionType);
            UpdateNextUpdateTimeText(missionType);
            UpdateMissionNextUpdateTime();
        }

        void RemoveCurrentContent()
        {
            ViewController.CurrentContentViewController?.Dismiss();
        }

        void ShowCurrentContent(MissionType currentMissionType)
        {
            var controller = CreateContentViewController(currentMissionType);
            if (controller == null)
            {
                Toast.MakeText("未対応の画面を表示しようとしました").Show();
                return;
            }

            ViewController.ShowCurrentContent(currentMissionType, controller, worldPositionStays: false);
            _currentMissionType = currentMissionType;
        }

        UIViewController CreateContentViewController(MissionType currentMissionType)
        {
            return currentMissionType switch
            {
                MissionType.Event => CreateEventAchievementMissionViewController(),
                MissionType.EventDailyBonus => CreateEventDailyBonusViewController(),
                _ => throw new ArgumentOutOfRangeException()
            };
        }

        EventAchievementMissionViewController CreateEventAchievementMissionViewController()
        {
            // イベントミッションのUIViewControllerを作る処理を書く
            var argument = new EventAchievementMissionViewController.Argument(
                _missionMainViewModel.EventAchievementMissionViewModel,
                Argument.MstEventId,
                Argument.IsEventMissionOpenInHome);
            var controller = ViewFactory.Create<
                EventAchievementMissionViewController,
                EventAchievementMissionViewController.Argument>(argument);
            controller.OnReceivedAction = UpdateEventAchievementMissionViewModel;
            return controller;
        }

        EventDailyBonusViewController CreateEventDailyBonusViewController()
        {
            // イベントミッションのUIViewControllerを作る処理を書く
            var argument =
                new EventDailyBonusViewController.Argument(
                    _missionMainViewModel.EventDailyBonusViewModel);
            var controller = ViewFactory.Create<EventDailyBonusViewController, EventDailyBonusViewController.Argument>(argument);
            controller.OnReceivedAction = UpdateEventDailyBonusViewModel;
            return controller;
        }

        void UpdateEventAchievementMissionViewModel(EventAchievementMissionViewModel viewModel)
        {
            _missionMainViewModel = _missionMainViewModel with
            {
                EventAchievementMissionViewModel = viewModel
            };
        }

        void UpdateEventDailyBonusViewModel(EventDailyBonusViewModel viewModel)
        {
            _missionMainViewModel = _missionMainViewModel with
            {
                EventDailyBonusViewModel = viewModel
            };
        }

        MissionType GetDisplayTab()
        {
            // NOTE: イベントデイリーボーナス演出時はイベントデイリーボーナスを表示、それ以外はイベント
            if (Argument.DailyBonusAnimationPlaying)
            {
                return MissionType.EventDailyBonus;
            }
            else
            {
                return MissionType.Event;
            }
        }

        void UpdateMissionNextUpdateTime()
        {
            _updateNextUpdateTimeCancellationTokenSource?.Cancel();
            _updateNextUpdateTimeCancellationTokenSource?.Dispose();
            
            _updateNextUpdateTimeCancellationTokenSource = new CancellationTokenSource();
            
            DoAsync.Invoke(_updateNextUpdateTimeCancellationTokenSource.Token, async cancellationToken =>
            {
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    await UniTask.Delay(TimeSpan.FromSeconds(1), cancellationToken: cancellationToken);
                    UpdateNextUpdateTimeText(_currentMissionType);
                }
            });
        }

        void UpdateNextUpdateTimeText(MissionType currentMissionType)
        {
            var timeInformation = GetEventMissionTimeInformationUseCase
                .GetEventMissionTimeInformation(_eventMissionCommonHeaderViewModel.MstEventId);

            ViewController.SetUpHeaderRemainingTime(currentMissionType == MissionType.EventDailyBonus
                ? timeInformation.RemainingDailyBonusTimeSpan
                : timeInformation.RemainingEventTimeSpan);
        }
        
        void SetUpEventBanner()
        {
            ViewController.SetUpHeaderBannerImage(
                _eventMissionCommonHeaderViewModel,
                Argument.DailyBonusAnimationPlaying);
        }
    }
}
