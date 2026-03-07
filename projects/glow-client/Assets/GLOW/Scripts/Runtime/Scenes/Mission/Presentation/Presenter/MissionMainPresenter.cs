using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Translator;
using GLOW.Scenes.Mission.Presentation.View.AchievementMission;
using GLOW.Scenes.Mission.Presentation.View.DailyBonusMission;
using GLOW.Scenes.Mission.Presentation.View.DailyMission;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.View.WeeklyMission;
using GLOW.Scenes.Mission.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.Presenter
{
    public class MissionMainPresenter : IMissionMainViewDelegate
    {
        [Inject] MissionMainViewController.Argument Argument { get; }
        [Inject] MissionMainViewController ViewController { get; }
        [Inject] FetchMissionListUseCase FetchMissionListUseCase { get; }
        [Inject] GetMissionNextUpdateTimeUseCase GetMissionNextUpdateTimeUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] MissionScreenInteractionControl MissionScreenInteractionControl { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        MissionViewModel _missionViewModel;

        MissionType _currentMissionType;
        
        bool _isDailyBonusAnimationPlaying = false;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(MissionMainPresenter), nameof(OnViewDidLoad));
            _isDailyBonusAnimationPlaying = Argument.IsFirstLogin;

            DoAsync.Invoke(ViewController.ActualView.GetCancellationTokenOnDestroy(),
                MissionScreenInteractionControl,
                async cancellationToken =>
                {
                    ViewController.SetCloseButtonInteractable(!_isDailyBonusAnimationPlaying);
                    
                    await FetchMissionList(cancellationToken);

                    SetBadgeVisible();

                    if (Argument.IsDisplayFromItemDetailLocation)
                    {
                        // ItemDetailから遷移してきた場合は、引数で指定されたタブを表示
                        _currentMissionType = Argument.MissionType;
                    }
                    else
                    {
                        _currentMissionType = GetActiveMissionTab();
                    }

                    ViewController.SetTitleText(_currentMissionType);
                    ShowCurrentContent(_currentMissionType);
                    
                    _isDailyBonusAnimationPlaying = false;
                });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(MissionMainPresenter), nameof(OnViewDidUnload));
        }

        public void OnDailyBonusMissionTabSelected()
        {
            SwitchMissionContent(MissionType.DailyBonus);
        }

        public void OnDailyMissionTabSelected()
        {
            SwitchMissionContent(MissionType.Daily);
        }

        public void OnWeeklyMissionTabSelected()
        {
            SwitchMissionContent(MissionType.Weekly);
        }

        public void OnAchievementMissionTabSelected()
        {
            SwitchMissionContent(MissionType.Achievement);
        }

        public void OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        public void OnEscape()
        {
            ApplicationLog.Log(nameof(MissionMainPresenter), nameof(OnEscape));
            
            if (_isDailyBonusAnimationPlaying)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            ViewController.CloseView();
        }

        public async UniTask<IMissionViewModel> FetchMissionList(CancellationToken cancellationToken)
        {
            var missionModel = await FetchMissionListUseCase.FetchMissionList(cancellationToken);

            // 通知のスケジュールを更新
            LocalNotificationScheduler.RefreshDailyMissionSchedule();

            _missionViewModel =
                new MissionViewModel(
                    AchievementMissionViewModelTranslator.ToAchievementMissionViewModel(missionModel
                        .AchievementResultModel),
                    DailyBonusMissionViewModelTranslator.ToDailyBonusMissionViewModel(
                        missionModel.DailyBonusResultModel,
                        GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.DailyBonus)),
                    DailyMissionViewModelTranslator.ToDailyMissionViewModel(
                        missionModel.DailyResultModel,
                        GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.Daily)),
                    WeeklyMissionViewModelTranslator.ToWeeklyMissionViewModel(
                        missionModel.WeeklyResultModel,
                        GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.Weekly)));

            return _missionViewModel;
        }

        void SetBadgeVisible()
        {
            ViewController.ActualView.SetBadgeVisible(
                MissionType.Achievement,
                _missionViewModel.AchievementMissionViewModel.IsReceivableRewardExist());
            ViewController.ActualView.SetBadgeVisible(MissionType.DailyBonus, false);
            ViewController.ActualView.SetBadgeVisible(
                MissionType.Daily,
                _missionViewModel.DailyMissionViewModel.IsReceivableRewardExist());
            ViewController.ActualView.SetBadgeVisible(
                MissionType.Weekly,
                _missionViewModel.WeeklyMissionViewModel.IsReceivableRewardExist());
        }

        void SwitchMissionContent(MissionType missionType)
        {
            if(_currentMissionType == missionType)
                return;

            RemoveCurrentContent();
            ShowCurrentContent(missionType);
            _currentMissionType = missionType;
        }

        void RemoveCurrentContent()
        {
            ViewController.CurrentContentViewController?.Dismiss();
        }

        void ShowCurrentContent(MissionType missionType)
        {
            var controller = CreateContentViewController(missionType);
            ViewController.ShowCurrentContent(missionType, controller, worldPositionStays: false);
        }

        UIViewController CreateContentViewController(MissionType missionType)
        {
            UIViewController controller;
            switch (missionType)
            {
                case MissionType.Achievement:
                {
                    var argument = new AchievementMissionViewController.Argument(
                        _missionViewModel.AchievementMissionViewModel,
                        UpdateAchievementMissionViewModel);
                    controller = ViewFactory.Create<
                        AchievementMissionViewController,
                        AchievementMissionViewController.Argument>(argument);
                }break;
                case MissionType.DailyBonus:
                {
                    var argument = new DailyBonusMissionViewController.Argument(
                        _missionViewModel.DailyBonusMissionViewModel,
                        UpdateDailyBonusMissionViewModel);
                    controller = ViewFactory.Create<
                        DailyBonusMissionViewController,
                        DailyBonusMissionViewController.Argument>(argument);
                }break;
                case MissionType.Daily:
                {
                    var argument = new DailyMissionViewController.Argument(
                        _missionViewModel.DailyMissionViewModel,
                        UpdateDailyMissionViewModel);
                    controller = ViewFactory.Create<
                        DailyMissionViewController,
                        DailyMissionViewController.Argument>(argument);
                }break;
                case MissionType.Weekly:
                {
                    var argument = new WeeklyMissionViewController.Argument(
                        _missionViewModel.WeeklyMissionViewModel,
                        UpdateWeeklyMissionViewModel);
                    controller = ViewFactory.Create<
                        WeeklyMissionViewController,
                        WeeklyMissionViewController.Argument>(argument);
                }break;
                default:
                    throw new ArgumentOutOfRangeException();
            }

            return controller;
        }

        void UpdateAchievementMissionViewModel(IAchievementMissionViewModel viewModel)
        {
            var currentMissionModel = _missionViewModel;
            _missionViewModel = new MissionViewModel(
                viewModel,
                currentMissionModel.DailyBonusMissionViewModel,
                currentMissionModel.DailyMissionViewModel,
                currentMissionModel.WeeklyMissionViewModel);
        }

        void UpdateDailyBonusMissionViewModel(IDailyBonusMissionViewModel viewModel)
        {
            var currentMissionModel = _missionViewModel;
            _missionViewModel = new MissionViewModel(
                currentMissionModel.AchievementMissionViewModel,
                viewModel,
                currentMissionModel.DailyMissionViewModel,
                currentMissionModel.WeeklyMissionViewModel);
        }

        void UpdateDailyMissionViewModel(IDailyMissionViewModel viewModel)
        {
            var currentMissionModel = _missionViewModel;
            _missionViewModel = new MissionViewModel(
                currentMissionModel.AchievementMissionViewModel,
                currentMissionModel.DailyBonusMissionViewModel,
                viewModel,
                currentMissionModel.WeeklyMissionViewModel);
        }

        void UpdateWeeklyMissionViewModel(IWeeklyMissionViewModel viewModel)
        {
            var currentMissionModel = _missionViewModel;
            _missionViewModel = new MissionViewModel(
                currentMissionModel.AchievementMissionViewModel,
                currentMissionModel.DailyBonusMissionViewModel,
                currentMissionModel.DailyMissionViewModel,
                viewModel);
        }

        MissionType GetActiveMissionTab()
        {
            // NOTE: デイリーボーナス > デイリー > ウィークリー > アチーブメント の優先順で最初のタブを表示、全部受け取り済みの場合はデイリー
            if (_missionViewModel.DailyBonusMissionViewModel.IsReceivableRewardExist() || Argument.IsFirstLogin)
            {
                return MissionType.DailyBonus;
            }
            else if (_missionViewModel.DailyMissionViewModel.IsReceivableRewardExist())
            {
                return MissionType.Daily;
            }
            else if (_missionViewModel.WeeklyMissionViewModel.IsReceivableRewardExist())
            {
                return MissionType.Weekly;
            }
            else if (_missionViewModel.AchievementMissionViewModel.IsReceivableRewardExist())
            {
                return MissionType.Achievement;
            }

            return MissionType.Daily;
        }
    }
}
