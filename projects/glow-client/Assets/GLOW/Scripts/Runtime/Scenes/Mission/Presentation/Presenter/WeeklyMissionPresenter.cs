using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Const;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Navigation;
using GLOW.Scenes.Mission.Presentation.Translator;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.View.WeeklyMission;
using GLOW.Scenes.Shop.Domain.Calculator;
using UnityEngine;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.Presenter
{
    public class WeeklyMissionPresenter : IWeeklyMissionViewDelegate
    {
        [Inject] IMissionMainControl MissionMainViewControl { get; }
        [Inject] WeeklyMissionViewController ViewController { get; }
        [Inject] WeeklyMissionViewController.Argument Argument { get; }
        [Inject] ReceiveMissionRewardUseCase ReceiveMissionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IMissionNavigator MissionNavigator { get; }
        [Inject] GetMissionNextUpdateTimeUseCase GetMissionNextUpdateTimeUseCase { get; }
        [Inject] BulkReceiveMissionRewardUseCase BulkReceiveMissionRewardUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ShowBonusPointMissionRewardReceivingUseCase ShowBonusPointMissionRewardReceivingUseCase { get; }

        CancellationToken WeeklyMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IWeeklyMissionViewDelegate.OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            ViewController.UpdateBonusPointComponent();

            UpdateNextUpdateTimeText();
            UpdateMissionNextUpdateTime();

            MissionMainViewControl.SetBulkReceiveAction(BulkReceive);
            MissionMainViewControl.SetBulkReceivable(Argument.ViewModel.IsReceivableRewardExist());
        }

        void IWeeklyMissionViewDelegate.ReceiveBonusPoint(MasterDataId weeklyMissionId)
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                cancellationToken => ReceiveRewardSingle(weeklyMissionId, cancellationToken),
                PlayReceiveAnimationAfterReceivedPoint);
        }

        public void BulkReceive()
        {
            // 一括受取りタイミングでのポイント総計
            var bonusPoint = Argument.ViewModel.WeeklyMissionCellViewModels
                .Where(c => c.MissionStatus == MissionStatus.Receivable ||
                            c.MissionStatus == MissionStatus.Received)
                .Sum(c => c.BonusPoint.Value);

            CommonReceiveWireFrame.AsyncShowReceived(
                ReceiveRewardBulk,
                PlayReceiveAnimationAfterReceivedPoint);
        }

        void IWeeklyMissionViewDelegate.ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            RectTransform windowPosition)
        {
            MissionMainViewControl
                .ShowRewardListWindow(viewModels, windowPosition, WeeklyMissionCancellationToken)
                .Forget();
        }

        void IWeeklyMissionViewDelegate.OnMissionBonusPointSelected()
        {
            ItemDetailWireFrame.ShowMissionBonusPointDetail(PlayerResourceConst.WeeklyBonusPointMasterDataId, ViewController);
        }

        void IWeeklyMissionViewDelegate.OnEscape()
        {
            if (!MissionMainViewControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            MissionMainViewControl.CloseView();
        }

        void IWeeklyMissionViewDelegate.OnChallenge(DestinationScene destination)
        {
            var destinationScene = destination.TryToEnum();
            switch (destinationScene)
            {
                case DestinationSceneEnum.Home:
                case DestinationSceneEnum.StageSelect:
                    MissionMainViewControl.DismissByChallenge();
                    break;
                case DestinationSceneEnum.QuestSelect:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowHomeQuestSelectView();
                    break;
                case DestinationSceneEnum.UnitList:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowUnitListView();
                    break;
                case DestinationSceneEnum.IdleIncentive:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowIdleIncentiveTopView();
                    break;
                case DestinationSceneEnum.OutpostEnhance:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowOutpostEnhanceView();
                    break;
                case DestinationSceneEnum.Gacha:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowGachaView(MasterDataId.Empty);
                    break;
                case DestinationSceneEnum.Event:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowContentTopView();
                    break;
                case DestinationSceneEnum.Pvp:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowPvpTopView();
                    break;
                case DestinationSceneEnum.Empty:
                    Toast.MakeText("設定されておりません。").Show();
                    break;
                default:
                    Toast.MakeText("まだ実装されていません。").Show();
                    break;
            }
        }

        // single...ミッション1件のみ。報酬件数が1件ではない
        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardSingle(
            MasterDataId weeklyMissionId,
            CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var model = await ReceiveMissionRewardUseCase.ReceiveMissionReward(
                    cancellationToken,
                    MissionType.Weekly,
                    weeklyMissionId);

                var missionViewModel = WeeklyMissionViewModelTranslator.ToWeeklyMissionViewModel(
                    model.MissionFetchResultModel.WeeklyResultModel,
                    GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(
                        MissionType.Weekly));
                Argument.OnReceivedAction?.Invoke(missionViewModel);

                // 画面更新
                ViewController.SetViewModel(missionViewModel);
                MissionMainViewControl.SetBadgeVisible(
                    MissionType.Weekly,
                    missionViewModel.IsReceivableRewardExist());
                MissionMainViewControl.SetBulkReceivable(missionViewModel.IsReceivableRewardExist());

                var viewModels = model.CommonReceiveResourceModels
                    .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
            });

            return receiveRewardTask;
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardBulk(CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var model = await BulkReceiveMissionRewardUseCase.BulkReceiveMissionReward(
                    cancellationToken,
                    MissionType.Weekly);

                var missionViewModel = WeeklyMissionViewModelTranslator.ToWeeklyMissionViewModel(
                    model.MissionFetchResultModel.WeeklyResultModel,
                    GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.Weekly));
                Argument.OnReceivedAction?.Invoke(missionViewModel);

                // 画面更新
                ViewController.SetViewModel(missionViewModel);
                MissionMainViewControl.SetBulkReceivable(missionViewModel.IsReceivableRewardExist());
                MissionMainViewControl.SetBadgeVisible(
                    MissionType.Weekly,
                    missionViewModel.IsReceivableRewardExist());

                var viewModels = model.CommonReceiveResourceModels
                    .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
            });

            return receiveRewardTask;
        }

        void PlayReceiveAnimationAfterReceivedPoint()
        {
            DoAsync.Invoke(WeeklyMissionCancellationToken, async cancellationToken =>
            {
                MissionMainViewControl.SetInteractable(false);

                var bonusPointReceivingInfo =
                    ShowBonusPointMissionRewardReceivingUseCase.GetReceivedBonusPointMissionRewardInfo(
                        MissionType.Weekly);
                if (bonusPointReceivingInfo.IsEmpty())
                {
                    MissionMainViewControl.SetInteractable(true);
                    return;
                }

                ViewController.SetupBonusPointGaugeRate(
                    bonusPointReceivingInfo.BeforeBonusPoint,
                    bonusPointReceivingInfo.MaxBonusPoint);

                var viewModel = ReceivedBonusPointMissionRewardInfoViewModelTranslator
                    .ToViewModel(bonusPointReceivingInfo);

                SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);

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

                MissionMainViewControl.SetInteractable(true);
            });
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

        void UpdateMissionNextUpdateTime()
        {
            DoAsync.Invoke(WeeklyMissionCancellationToken, async cancellationToken =>
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
            var nextUpdateTime = GetMissionNextUpdateTimeUseCase.GetNextUpdateTime(MissionType.Weekly);
            ViewController.UpdateMissionNextUpdateTime(nextUpdateTime);
        }
    }
}
