using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Const;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.BeginnerMission.Presentation.Translator;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Navigation;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Presentation.Presenter
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public class BeginnerMissionContentPresenter : IBeginnerMissionContentViewDelegate
    {
        [Inject] BeginnerMissionContentViewController ViewController { get; }
        [Inject] IBeginnerMissionMainControl BeginnerMissionMainViewControl { get; }
        [Inject] BeginnerMissionContentViewController.Argument Argument { get; }
        [Inject] ReceiveMissionRewardUseCase ReceiveMissionRewardUseCase { get; }
        [Inject] IMissionNavigator MissionNavigator { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] MissionClearOnCallUseCase MissionClearOnCallUseCase { get; }
        [Inject] BeginnerMissionScreenInteractionControl BeginnerMissionScreenInteractionControl { get; }

        public void OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            BeginnerMissionMainViewControl.SetIndicatorVisible(false);
        }

        void IBeginnerMissionContentViewDelegate.ReceiveMissionReward(IBeginnerMissionCellViewModel viewModel)
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                cancellationToken => ReceiveRewardSingle(
                    cancellationToken,
                    viewModel.BeginnerMissionId),
                () => { BeginnerMissionMainViewControl.PlayReceiveAnimationAfterReceivedPoints(); });
        }

        void IBeginnerMissionContentViewDelegate.OnEscape()
        {
            if (!BeginnerMissionMainViewControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            BeginnerMissionMainViewControl.CloseView();
        }

        void IBeginnerMissionContentViewDelegate.OnChallenge(
            MasterDataId mstBeginnerId,
            DestinationScene destination,
            CriterionValue criterionValue,
            Action onTransitionCompleted)
        {
            var destinationScene = destination.TryToEnum();
            var actionOnTransitionCompleted = new Action(() => { onTransitionCompleted?.Invoke(); });
            switch (destinationScene)
            {
                case DestinationSceneEnum.Home:
                case DestinationSceneEnum.StageSelect:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    break;
                case DestinationSceneEnum.QuestSelect:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowHomeQuestSelectView();
                    break;
                case DestinationSceneEnum.UnitList:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowUnitListView();
                    break;
                case DestinationSceneEnum.Web:
                    ViewController.SetOnApplicationFocusedAction(() =>
                    {
                        DoAsync.Invoke(
                            ViewController.ActualView,
                            BeginnerMissionScreenInteractionControl,
                            async cancellationToken =>
                            {
                                await ClearOnCall(mstBeginnerId, cancellationToken);
                                ViewController.ClearOnApplicationFocusedAction();
                            });
                    });
                    MissionNavigator.ShowUrl(criterionValue);
                    break;
                case DestinationSceneEnum.IdleIncentive:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowIdleIncentiveTopView();
                    break;
                case DestinationSceneEnum.OutpostEnhance:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowOutpostEnhanceView();
                    break;
                case DestinationSceneEnum.Gacha:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowGachaView(MasterDataId.Empty);
                    break;
                case DestinationSceneEnum.Event:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
                    MissionNavigator.ShowContentTopView();
                    break;
                case DestinationSceneEnum.Pvp:
                    BeginnerMissionMainViewControl.DismissByChallenge(actionOnTransitionCompleted);
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

        void IBeginnerMissionContentViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(playerResourceIconViewModel, ViewController);
        }

        void IBeginnerMissionContentViewDelegate.OnUnlockMissionButtonSelected()
        {
            CommonToastWireFrame.ShowScreenCenterToast("未開放の初心者ミッションになります。");
        }

        void IBeginnerMissionContentViewDelegate.OnMissionBonusPointSelected()
        {
            ItemDetailWireFrame.ShowMissionBonusPointDetail(PlayerResourceConst.BeginnerBonusPointMasterDataId, ViewController);
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardSingle(
            CancellationToken cancellationToken, 
            MasterDataId beginnerMissionId)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var receiveReward = await ReceiveMissionRewardUseCase.ReceiveMissionReward(
                    cancellationToken,
                    MissionType.Beginner,
                    beginnerMissionId);
                // ミッション受け取り後のローカル通知を再設定
                LocalNotificationScheduler.RefreshBeginnerMissionSchedule();

                var missionViewModel = BeginnerMissionMainViewModelTranslator.ToBeginnerMissionMainViewModel(
                    receiveReward.MissionFetchResultModel.BeginnerResultModel,
                    receiveReward.MissionFetchResultModel.BeginnerMissionDaysFromStart);
                ViewController.OnReceivedAction?.Invoke(missionViewModel);

                BeginnerMissionMainViewControl.SetBadgeVisible(
                    Argument.CurrentDayNumber,
                    missionViewModel.IsReceivableRewardExistFromDay(
                        Argument.CurrentDayNumber));

                BeginnerMissionMainViewControl.SetBulkReceivable(missionViewModel.IsReceivableRewardExist());

                var contentViewModel = BeginnerMissionContentViewModelTranslator.ToBeginnerMissionContentViewModel(
                    missionViewModel,
                    Argument.CurrentDayNumber);

                ViewController.SetViewModel(contentViewModel);
                BeginnerMissionMainViewControl.SetBonusPointViewModel(missionViewModel.BonusPointMissionViewModel);

                var rewards =
                    receiveReward.CommonReceiveResourceModels
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(rewards);
            });

            return receiveRewardTask;
        }
        
        async UniTask ClearOnCall(MasterDataId mstBeginnerId, CancellationToken cancellationToken)
        {
            var result = await MissionClearOnCallUseCase.ClearOnCall(
                cancellationToken,
                MissionType.Beginner,
                mstBeginnerId);

            var beginnerMissionMainViewModel = BeginnerMissionMainViewModelTranslator.ToBeginnerMissionMainViewModel(
                result.BeginnerResultModel,
                result.BeginnerMissionDaysFromStart);

            var contentViewModel = BeginnerMissionContentViewModelTranslator.ToBeginnerMissionContentViewModel(
                beginnerMissionMainViewModel,
                Argument.CurrentDayNumber);

            ViewController.SetViewModel(contentViewModel);
        }
    }
}
