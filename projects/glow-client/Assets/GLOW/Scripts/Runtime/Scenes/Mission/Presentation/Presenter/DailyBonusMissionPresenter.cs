using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Translator;
using GLOW.Scenes.Mission.Presentation.View.DailyBonusMission;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.Presenter
{
    public class DailyBonusMissionPresenter : IDailyBonusMissionViewDelegate
    {
        [Inject] IMissionMainControl MissionMainViewControl { get; }
        [Inject] DailyBonusMissionViewController ViewController { get; }
        [Inject] DailyBonusMissionViewController.Argument Argument { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] GetMissionNextUpdateTimeUseCase GetMissionNextUpdateTimeUseCase { get; }
        [Inject] AutoReceiveDailyBonusUseCase AutoReceiveDailyBonusUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        CancellationToken DailyBonusMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _isDailyBonusAnimationPlaying = false;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(DailyBonusMissionPresenter), nameof(OnViewDidLoad));

            ViewController.SetViewModel(Argument.ViewModel);

            UpdateNextUpdateTimeText();
            UpdateMissionNextUpdateTime();

            // 一括受け取りは非表示(自動受け取りの関係で使わないため)
            MissionMainViewControl.SetBulkReceiveAction(null);

            // デイリーボーナス受け取り時のアクションを自動で実行
            DoAsync.Invoke(DailyBonusMissionCancellationToken, ViewController, async cancellationToken =>
            {
                if (!Argument.ViewModel.IsReceivableRewardExist()) return;

                var receivedDailyBonusInfoModel = AutoReceiveDailyBonusUseCase.GetAutoReceiveDailyBonus();

                if (receivedDailyBonusInfoModel.IsEmpty()) return;

                _isDailyBonusAnimationPlaying = true;

                var dailyBonusResourceViewModels =receivedDailyBonusInfoModel.DailyBonusRewards
                    .Select(r =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();

                var nextUpdateTime = GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.DailyBonus);
                var missionViewModel = DailyBonusMissionViewModelTranslator.ToDailyBonusMissionViewModel(
                    receivedDailyBonusInfoModel.MissionFetchResult.DailyBonusResultModel,
                    nextUpdateTime);

                Argument.OnReceivedAction?.Invoke(missionViewModel);

                await PlayDailyBonusReceiveAnimation(
                    cancellationToken,
                    missionViewModel,
                    receivedDailyBonusInfoModel.LoginDayCount,
                    dailyBonusResourceViewModels);
                
                _isDailyBonusAnimationPlaying = false;
                MissionMainViewControl.SetCloseButtonInteractable(!_isDailyBonusAnimationPlaying);
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(DailyBonusMissionPresenter), nameof(OnViewDidLoad));
        }

        public void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition)
        {
            MissionMainViewControl
                .ShowRewardListWindow(viewModels, windowPosition, DailyBonusMissionCancellationToken)
                .Forget();
        }

        void IDailyBonusMissionViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(playerResourceIconViewModel, ViewController);
        }

        public void OnEscape()
        {
            if (!MissionMainViewControl.Interactable || _isDailyBonusAnimationPlaying)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            MissionMainViewControl.CloseView();
        }

        async UniTask PlayDailyBonusReceiveAnimation(
            CancellationToken cancellationToken,
            IDailyBonusMissionViewModel dailyBonusViewModel,
            LoginDayCount loginDayCount,
            IReadOnlyList<CommonReceiveResourceViewModel> dailyBonusResourceResourceViewModels)
        {
            await UniTask.Delay(500, cancellationToken: cancellationToken);

            await ViewController.PlayDailyBonusStampAnimation(
                cancellationToken,
                loginDayCount);
            ViewController.SetViewModel(dailyBonusViewModel);

            MissionMainViewControl.SetBadgeVisible(
                MissionType.DailyBonus,
                dailyBonusViewModel.IsReceivableRewardExist());

            HomeHeaderDelegate.UpdateStatus();

            await CommonReceiveWireFrame.ShowAsync(
                cancellationToken,
                dailyBonusResourceResourceViewModels,
                RewardTitle.Default,
                ReceivedRewardDescription.Empty);
        }

        void UpdateMissionNextUpdateTime()
        {
            DoAsync.Invoke(DailyBonusMissionCancellationToken, async cancellationToken =>
            {
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    await UniTask.Delay(TimeSpan.FromSeconds(1), cancellationToken: cancellationToken);
                    UpdateNextUpdateTimeText();
                }
            });
        }

        void UpdateNextUpdateTimeText()
        {
            var nextUpdateTime = GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.DailyBonus);
            ViewController.UpdateMissionNextUpdateTime(nextUpdateTime);
        }
    }
}
