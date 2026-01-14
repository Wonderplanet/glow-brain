using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.BeginnerMission.Presentation.Translator;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Translator;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Presentation.Presenter
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public class BeginnerMissionMainPresenter : IBeginnerMissionMainViewDelegate
    {
        [Inject] IBeginnerMissionMainControl MissionMainViewControl { get; }
        [Inject] BeginnerMissionMainViewController ViewController { get; }
        [Inject] FetchMissionListUseCase FetchMissionListUseCase { get; }
        [Inject] CheckBeginnerMissionDayUnlockUseCase CheckBeginnerMissionDayUnlockUseCase { get; }
        [Inject] BulkReceiveMissionRewardUseCase BulkReceiveMissionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] GetBeginnerMissionPromptPhraseUseCase GetBeginnerMissionPromptPhraseUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ShowBonusPointMissionRewardReceivingUseCase ShowBonusPointMissionRewardReceivingUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        IBeginnerMissionMainViewModel _missionMainViewModel;

        BeginnerMissionDayNumber _currentDayNumber;

        CancellationToken BeginnerMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        public void OnViewDidLoad()
        {
            DoAsync.Invoke(
                BeginnerMissionCancellationToken,
                async cancellationToken =>
                {
                    // 通信段階では一括受け取りを押せなくする
                    MissionMainViewControl.SetBulkReceivable(false);

                    // ミッションのリストを取得
                    await FetchMissionList(cancellationToken);

                    // 通信後に表示する
                    MissionMainViewControl.SetMissionComponentVisible(true);

                    // 未開放の日付の場合は鍵アイコンを表示
                    SetUpLockIconVisible();

                    // 報酬受け取り可能なミッションがある場合はバッジ表示
                    SetTabBadgesVisible();

                    // 最初に選択されている状態のタブの日付を取得
                    SetSelectedTab();

                    // 日毎に設定されているミッションリストを表示
                    ShowCurrentContent(_currentDayNumber);

                    // 共通のポイントミッションを表示
                    MissionMainViewControl.SetBonusPointViewModel(_missionMainViewModel.BonusPointMissionViewModel);
                    ViewController.UpdateBonusPointComponent();

                    // 初心者ミッションの文言の設定
                    SetReceivableTotalDiamondAmount();

                    // 一括受け取りを押せるかどうか
                    MissionMainViewControl.SetBulkReceivable(_missionMainViewModel.IsReceivableRewardExist());

                    // 日が開放される場合はアニメーションの表示
                    MissionMainViewControl.SetInteractable(false);
                    await BeginnerMissionUnlockDayAnimation(cancellationToken);
                    MissionMainViewControl.SetInteractable(true);
                });
        }

        void IBeginnerMissionMainViewDelegate.BulkReceive()
        {
            // 一括受取りタイミングでのポイント総計
            _missionMainViewModel.BeginnerMissionCellViewModelsDictionary.TryGetValue(_currentDayNumber, out var cellViewModels);
            var bonusPoint = cellViewModels?.Where(c =>
                    c.MissionStatus == MissionStatus.Receivable || c.MissionStatus == MissionStatus.Received)
                .Sum(c => c.BonusPoint.Value);

            CommonReceiveWireFrame.AsyncShowReceived(
                ReceiveRewardBulk,
                PlayReceiveAnimationAfterReceivedPoints);
        }

        void IBeginnerMissionMainViewDelegate.ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition)
        {
            MissionMainViewControl
                .ShowRewardListWindow(viewModels, windowPosition, BeginnerMissionCancellationToken)
                .Forget();
        }

        void IBeginnerMissionMainViewDelegate.OnSelectRewardInWindow(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void IBeginnerMissionMainViewDelegate.OnDayNumberTabSelected(BeginnerMissionDayNumber dayNumber)
        {
            SwitchMissionContent(dayNumber);
        }

        void IBeginnerMissionMainViewDelegate.OnEscape()
        {
            ViewController.CloseView();
        }

        public void PlayReceiveAnimationAfterReceivedPoints()
        {
            DoAsync.Invoke(BeginnerMissionCancellationToken, async cancellationToken =>
            {
                MissionMainViewControl.SetInteractable(false);

                var bonusPointReceivingInfo = ShowBonusPointMissionRewardReceivingUseCase.GetReceivedBonusPointMissionRewardInfo(
                    MissionType.Beginner);
                if (bonusPointReceivingInfo.IsEmpty())
                {
                    MissionMainViewControl.SetInteractable(true);
                    return;
                }

                SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);

                ViewController.SetupBonusPointGaugeRate(
                    bonusPointReceivingInfo.BeforeBonusPoint,
                    bonusPointReceivingInfo.MaxBonusPoint);

                var viewModel = ReceivedBonusPointMissionRewardInfoViewModelTranslator
                    .ToViewModel(bonusPointReceivingInfo);

                await ViewController.PlayBonusPointGaugeAnimation(
                    cancellationToken,
                    viewModel.UpdatedBonusPoint,
                    viewModel.MaxBonusPoint);

                await OpenRewardBoxAnimation(cancellationToken, viewModel.ReceivedRewardBonusPoints);
                ViewController.UpdateBonusPointComponent();

                HomeHeaderDelegate.UpdateStatus();

                if (!viewModel.ReceivedBonusPointMissionRewards.IsEmpty())
                {
                    CommonReceiveWireFrame.Show(
                        viewModel.ReceivedBonusPointMissionRewards,
                        onClosed: () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
                }
                else
                {
                    HomeHeaderDelegate.PlayExpGaugeAnimation();
                }

                MissionMainViewControl.SetInteractable(true);
            });
        }

        async UniTask FetchMissionList(CancellationToken cancellationToken)
        {
            var missionModel = await FetchMissionListUseCase.FetchMissionList(cancellationToken);
            // 通知のスケジュールを更新
            LocalNotificationScheduler.RefreshBeginnerMissionSchedule();
            _missionMainViewModel = BeginnerMissionMainViewModelTranslator.ToBeginnerMissionMainViewModel(
                missionModel.BeginnerResultModel,
                missionModel.BeginnerMissionDaysFromStart);
        }

        async UniTask BeginnerMissionUnlockDayAnimation(CancellationToken cancellationToken)
        {
            var unlockDayModel = CheckBeginnerMissionDayUnlockUseCase.CheckBeginnerMissionStartDay(
                _missionMainViewModel.CurrentDaysFromStart);
            if (unlockDayModel == null) return;
            if (!unlockDayModel.IsUnlockDay) return;

            await MissionMainViewControl.OpenUnlockDayAnimation(
                unlockDayModel.PreviousDaysFromStart,
                unlockDayModel.CurrentDaysFromStart,
                cancellationToken);
        }

        void SetReceivableTotalDiamondAmount()
        {
            var promptPhrase = GetBeginnerMissionPromptPhraseUseCase.GetBeginnerMissionReceivableTotalDiamondAmount();
            MissionMainViewControl.SetReceivableTotalDiamondAmount(promptPhrase.BeginnerMissionPromptPhrase);
        }

        void SetTabBadgesVisible()
        {
            const int startDay = 1;
            const int endDay = 7;
            for (var i = startDay; i <= endDay; i++)
            {
                SetTabBadgeVisible(new BeginnerMissionDayNumber(i));
            }
        }

        void SetTabBadgeVisible(BeginnerMissionDayNumber number)
        {
            // 未開放の日付の場合はバッジは非表示
            MissionMainViewControl.SetBadgeVisible(
                number,
                _missionMainViewModel.IsReceivableRewardExistFromDay(number) && IsUnlockDay(number));
        }

        void SetUpLockIconVisible()
        {
            _currentDayNumber = new BeginnerMissionDayNumber(_missionMainViewModel.CurrentDaysFromStart.Value);
            ViewController.SetUpLockIconVisible(_missionMainViewModel.CurrentDaysFromStart);
        }

        void SwitchMissionContent(BeginnerMissionDayNumber dayNumber)
        {
            if (_currentDayNumber == dayNumber)
                return;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            RemoveCurrentContent();
            ShowCurrentContent(dayNumber);
            _currentDayNumber = dayNumber;
        }

        void RemoveCurrentContent()
        {
            ViewController.CurrentContentViewController?.Dismiss();
        }

        void ShowCurrentContent(BeginnerMissionDayNumber dayNumber)
        {
            var controller = CreateContentViewController(dayNumber);
            ViewController.ShowCurrentContent(dayNumber, controller, worldPositionStays: false);
        }

        BeginnerMissionContentViewController CreateContentViewController(BeginnerMissionDayNumber dayNumber)
        {
            var contentViewModel = BeginnerMissionContentViewModelTranslator.ToBeginnerMissionContentViewModel(
                _missionMainViewModel,
                dayNumber);

            var argument = new BeginnerMissionContentViewController.Argument(
                contentViewModel,
                dayNumber);

            var controller = ViewFactory.Create<
                BeginnerMissionContentViewController,
                BeginnerMissionContentViewController.Argument>(argument);
            controller.OnReceivedAction = UpdateMissionMainViewModel;
            return controller;
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardBulk(CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var receiveReward = await BulkReceiveMissionRewardUseCase.BulkReceiveMissionReward(
                    cancellationToken,
                    MissionType.Beginner);
                LocalNotificationScheduler.RefreshBeginnerMissionSchedule();

                HomeHeaderDelegate.UpdateStatus();

                // 一括受け取りをしたので、中のリストも表示を更新する
                _missionMainViewModel = BeginnerMissionMainViewModelTranslator.ToBeginnerMissionMainViewModel(
                    receiveReward.MissionFetchResultModel.BeginnerResultModel,
                    receiveReward.MissionFetchResultModel.BeginnerMissionDaysFromStart);

                var contentViewModel = BeginnerMissionContentViewModelTranslator.ToBeginnerMissionContentViewModel(
                    _missionMainViewModel,
                    _currentDayNumber);
                ViewController.CurrentContentViewController.SetViewModel(contentViewModel);
                MissionMainViewControl.SetBonusPointViewModel(_missionMainViewModel.BonusPointMissionViewModel);

                SetTabBadgesVisible();
                MissionMainViewControl.SetBulkReceivable(_missionMainViewModel.IsReceivableRewardExist());

                var rewards =
                    receiveReward.CommonReceiveResourceModels
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(rewards);
            });

            return receiveRewardTask;
        }

        async UniTask OpenRewardBoxAnimation(CancellationToken cancellationToken, IReadOnlyList<BonusPoint> bonusPoints)
        {
            var rewardBoxAnimationList = new List<UniTask>();
            foreach (var point in bonusPoints)
            {
                rewardBoxAnimationList.Add(ViewController.OpenRewardBoxAnimation(cancellationToken, point));
            }

            await UniTask.WhenAll(rewardBoxAnimationList);
        }

        void UpdateMissionMainViewModel(IBeginnerMissionMainViewModel viewModel)
        {
            _missionMainViewModel = viewModel;
        }

        void SetSelectedTab()
        {
            // NOTE: 獲得報酬があってかつ一番日の数字が小さいタブを初期タブとする。タブ内に獲得報酬がない場合は一番新しい日を初期タブとする
            var receivableDayNumbers = _missionMainViewModel.BeginnerMissionCellViewModelsDictionary
                .Where(x => x.Value.Any(y => y.MissionStatus == MissionStatus.Receivable))
                .Select(x => x.Key)
                .ToList();
            if (!receivableDayNumbers.Any())
            {
                _currentDayNumber = new BeginnerMissionDayNumber(_missionMainViewModel.CurrentDaysFromStart.Value);
                return;
            }

            _currentDayNumber = BeginnerMissionDayNumber.Min(receivableDayNumbers.Min(), _currentDayNumber);
        }

        bool IsUnlockDay(BeginnerMissionDayNumber dayNumber)
        {
            return _missionMainViewModel.CurrentDaysFromStart.IsUnlockDay(dayNumber);
        }
    }
}
