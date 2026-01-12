using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.EventMission.Presentation.Tranalator;
using GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Presentation.Navigation;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.Presenter
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-2_アチーブメント（累計ミッション）
    /// </summary>
    public class EventAchievementMissionPresenter : IEventAchievementMissionViewDelegate
    {
        [Inject] EventAchievementMissionViewController ViewController { get; }
        [Inject] EventAchievementMissionViewController.Argument Argument { get; }
        [Inject] IEventMissionMainControl EventMissionMainViewControl { get; }
        [Inject] ReceiveEventMissionRewardUseCase ReceiveEventMissionRewardUseCase { get; }
        [Inject] BulkReceiveEventMissionRewardUseCase BulkReceiveEventMissionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IMissionNavigator MissionNavigator { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] GetEventMissionTimeInformationUseCase GetEventMissionTimeInformationUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }

        CancellationToken EventAchievementMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IEventAchievementMissionViewDelegate.OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            EventMissionMainViewControl.SetBulkReceiveButtonInteractable(Argument.ViewModel.IsReceivableRewardExist());
            EventMissionMainViewControl.SetBulkReceiveAction(BulkReceive);
            EventMissionMainViewControl.SetBulkReceiveVisible(true);
        }

        void IEventAchievementMissionViewDelegate.ReceiveMissionReward(IEventMissionCellViewModel viewModel)
        {
            if (!IsEventOpening(viewModel.EventId))
            {
                MessageViewUtil.ShowMessageWithClose(
                    "確認",
                    "開催期間外のミッションです。\n報酬を受け取れませんでした。",
                    null,
                    () =>
                    {
                        EventMissionMainViewControl.CloseView();
                    });
                return;
            }

            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardFunc(CancellationToken cancellationToken)
            {
                var receiveRewardTask = UniTask.Create(async () =>
                {
                    var receiveReward = await ReceiveEventMissionRewardUseCase.ReceiveEventMissionReward(
                        cancellationToken,
                        MissionType.Event,
                        viewModel.EventMissionId,
                        viewModel.EventId,
                        Argument.DisplayMissionMstEventId);

                    HomeHeaderDelegate.UpdateStatus();

                    var missionViewModel =
                        EventMissionViewModelTranslator.ToEventAchievementMissionViewModel(
                            receiveReward.EventMissionFetchResultModel.MstEventIdForTimeInformation,
                            receiveReward.EventMissionFetchResultModel.AchievementResultModel);
                    ViewController.OnReceivedAction?.Invoke(missionViewModel);
                    ViewController.SetViewModel(missionViewModel);
                    EventMissionMainViewControl.SetBadgeVisible(
                        MissionType.Event,
                        missionViewModel.IsReceivableRewardExist());
                    EventMissionMainViewControl.SetBulkReceiveButtonInteractable(missionViewModel.IsReceivableRewardExist());

                    var viewModels =
                        receiveReward.CommonReceiveResourceModels
                            .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                            .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });

                return receiveRewardTask;
            }

            CommonReceiveWireFrame.AsyncShowReceived(ReceiveRewardFunc, () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
        }

        void IEventAchievementMissionViewDelegate.OnChallenge(
            IEventMissionCellViewModel viewModel,
            Action onTransitionCompleted)
        {
            if (!IsEventOpening(viewModel.EventId))
            {
                MessageViewUtil.ShowMessageWithClose(
                    "確認",
                    "開催期間外のため挑戦できません。",
                    null,
                    () =>
                    {
                        EventMissionMainViewControl.CloseView();
                    });
                return;
            }

            var destinationScene = viewModel.DestinationScene.TryToEnum();
            var actionOnTransitionCompleted = new Action(() => { onTransitionCompleted?.Invoke(); });

            switch (destinationScene)
            {
                case DestinationSceneEnum.Home:
                case DestinationSceneEnum.StageSelect:
                    EventMissionMainViewControl.DismissByChallenge();
                    break;
                case DestinationSceneEnum.QuestSelect:
                    EventMissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowHomeQuestSelectView();
                    break;
                case DestinationSceneEnum.UnitList:
                    EventMissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowUnitListView();
                    break;
                case DestinationSceneEnum.IdleIncentive:
                    EventMissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowIdleIncentiveTopView();
                    break;
                case DestinationSceneEnum.OutpostEnhance:
                    EventMissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowOutpostEnhanceView();
                    break;
                case DestinationSceneEnum.Gacha:
                    EventMissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowGachaView(MasterDataId.Empty);
                    break;
                case DestinationSceneEnum.Event:
                    EventMissionMainViewControl.DismissByChallenge();
                    if (Argument.IsEventMissionOpenInHome)
                    {
                        MissionNavigator.ShowContentTopView();
                    }

                    break;
                case DestinationSceneEnum.Pvp:
                    EventMissionMainViewControl.DismissByChallenge();
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

        void IEventAchievementMissionViewDelegate.OnRewardIconSelected(
            PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(playerResourceIconViewModel, ViewController);
        }

        void IEventAchievementMissionViewDelegate.OnEscape()
        {
            if (!EventMissionMainViewControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            EventMissionMainViewControl.CloseView();
        }

        bool IsEventOpening(MasterDataId mstEventId)
        {
            var eventModel = GetEventMissionTimeInformationUseCase
                .GetEventMissionTimeInformation(mstEventId);

            return !eventModel.RemainingEventTimeSpan.IsEmpty();
        }

        void BulkReceive()
        {
            var hasExpired = Argument.ViewModel.TargetMstEventIds
                .Exists(m => !EventOpenCheckUseCase.IsOpenEvent(m));

            if (hasExpired)
            {
                MessageViewUtil.ShowMessageWithClose(
                    "確認",
                    "開催期間外のミッションがあります。\n報酬を受け取れませんでした。",
                    null,
                    () =>
                    {
                        EventMissionMainViewControl.CloseView();
                    });
                return;
            }

            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardFunc(CancellationToken cancellationToken)
            {
                var receiveRewardTask = UniTask.Create(async () =>
                {
                    var receiveReward = await BulkReceiveEventMissionRewardUseCase.BulkReceiveMissionReward(
                        cancellationToken,
                        Argument.ViewModel.TargetMstEventIds,
                        Argument.DisplayMissionMstEventId);

                    HomeHeaderDelegate.UpdateStatus();
                    var missionViewModel =
                        EventMissionViewModelTranslator.ToEventAchievementMissionViewModel(
                            receiveReward.EventMissionFetchResultModel.MstEventIdForTimeInformation,
                            receiveReward.EventMissionFetchResultModel.AchievementResultModel);
                    ViewController.OnReceivedAction?.Invoke(missionViewModel);
                    ViewController.SetViewModel(missionViewModel);
                    EventMissionMainViewControl.SetBadgeVisible(
                        MissionType.Event,
                        missionViewModel.IsReceivableRewardExist());

                    EventMissionMainViewControl.SetBulkReceiveButtonInteractable(missionViewModel.IsReceivableRewardExist());

                    var viewModels = receiveReward.CommonReceiveResourceModels
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });

                return receiveRewardTask;
            }

            CommonReceiveWireFrame.AsyncShowReceived(ReceiveRewardFunc, () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
        }
    }
}
