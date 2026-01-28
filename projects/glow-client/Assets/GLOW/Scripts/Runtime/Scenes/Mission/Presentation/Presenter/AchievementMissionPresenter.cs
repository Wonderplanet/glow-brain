using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Modules;
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
using GLOW.Scenes.Mission.Presentation.View.AchievementMission;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;
using GLOW.Scenes.Shop.Domain.Calculator;
using WonderPlanet.UniTaskSupporter;
using Zenject;
using Toast = WonderPlanet.ToastNotifier.Toast;

namespace GLOW.Scenes.Mission.Presentation.Presenter
{
    public class AchievementMissionPresenter : IAchievementMissionViewDelegate
    {
        [Inject] IMissionMainControl MissionMainViewControl { get; }
        [Inject] AchievementMissionViewController ViewController { get; }
        [Inject] AchievementMissionViewController.Argument Argument { get; }
        [Inject] ReceiveMissionRewardUseCase ReceiveMissionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IMissionNavigator MissionNavigator { get; }
        [Inject] BulkReceiveMissionRewardUseCase BulkReceiveMissionRewardUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IMissionMainViewDelegate MissionMainViewDelegate { get; }
        [Inject] MissionScreenInteractionControl MissionScreenInteractionControl { get; }
        [Inject] MissionClearOnCallUseCase MissionClearOnCallUseCase { get; }

        CancellationToken AchievementMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IAchievementMissionViewDelegate.OnViewDidLoad()
        {
            ViewController.SetViewModel(Argument.ViewModel);
            MissionMainViewControl.SetBulkReceiveAction(BulkReceive);
            MissionMainViewControl.SetBulkReceivable(Argument.ViewModel.IsReceivableRewardExist());
        }

        void IAchievementMissionViewDelegate.ReceiveReward(IAchievementMissionCellViewModel viewModel, Action onReceiveComplete)
        {
            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardFunc(CancellationToken cancellationToken)
            {
                var receiveRewardTask = UniTask.Create(async () =>
                {
                    var model =
                        await ReceiveMissionRewardUseCase.ReceiveMissionReward(
                            cancellationToken,
                            MissionType.Achievement,
                            viewModel.AchievementMissionId);
                    HomeHeaderDelegate.UpdateStatus();

                    var viewModels = model.CommonReceiveResourceModels
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });

                return receiveRewardTask;
            }

            CommonReceiveWireFrame.AsyncShowReceived(
                ReceiveRewardFunc,
                () =>
                {
                    HomeHeaderDelegate.PlayExpGaugeAnimation();
                },
                UpdateMissionList);
        }

        public void BulkReceive()
        {
            UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardFunc(CancellationToken cancellationToken)
            {
                var receiveRewardTask = UniTask.Create(async () =>
                {
                    var receiveReward =
                        await BulkReceiveMissionRewardUseCase.BulkReceiveMissionReward(
                            cancellationToken,
                            MissionType.Achievement);
                    HomeHeaderDelegate.UpdateStatus();

                    var viewModels = receiveReward.CommonReceiveResourceModels
                        .Select(m =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModels);
                });

                return receiveRewardTask;
            }

            CommonReceiveWireFrame.AsyncShowReceived(
                ReceiveRewardFunc,
                () =>
                {
                    HomeHeaderDelegate.PlayExpGaugeAnimation();
                }, 
                UpdateMissionList);
        }

        void UpdateMissionList()
        {
            DoAsync.Invoke(AchievementMissionCancellationToken, MissionScreenInteractionControl, async cancellationToken =>
            {
                var missionViewModel = await MissionMainViewDelegate.FetchMissionList(cancellationToken);

                ViewController.SetViewModel(missionViewModel.AchievementMissionViewModel);
                MissionMainViewControl.SetBadgeVisible(
                    MissionType.Achievement,
                    missionViewModel.AchievementMissionViewModel.IsReceivableRewardExist());
                MissionMainViewControl.SetBulkReceivable(missionViewModel.AchievementMissionViewModel.IsReceivableRewardExist());
            });
        }

        void IAchievementMissionViewDelegate.OnEscape()
        {
            if (!MissionMainViewControl.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            MissionMainViewControl.CloseView();
        }

        void IAchievementMissionViewDelegate.OnChallenge(
            MasterDataId mstAchievementId,
            DestinationScene destination,
            CriterionValue criterionValue)
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
                case DestinationSceneEnum.Web:
                    ViewController.SetOnApplicationFocusedAction(() =>
                    {
                        DoAsync.Invoke(
                            ViewController.ActualView,
                            MissionScreenInteractionControl,
                            async cancellationToken =>
                            {
                                await ClearOnCall(mstAchievementId, cancellationToken);
                                ViewController.ClearOnApplicationFocusedAction();
                            });
                    });
                    MissionNavigator.ShowUrl(criterionValue);
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
                case DestinationSceneEnum.LinkBnId:
                    MissionMainViewControl.DismissByChallenge();
                    MissionNavigator.ShowLinkBnIdView();
                    break;
                case DestinationSceneEnum.Empty:
                    Toast.MakeText("設定されておりません。").Show();
                    break;
                default:
                    Toast.MakeText("まだ実装されていません。").Show();
                    break;
            }
        }

        void IAchievementMissionViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(playerResourceIconViewModel, ViewController);
        }

        async UniTask ClearOnCall(MasterDataId mstAchievementId, CancellationToken cancellationToken)
        {
            var result = await MissionClearOnCallUseCase.ClearOnCall(
                cancellationToken,
                MissionType.Achievement,
                mstAchievementId);

            var viewModel = AchievementMissionViewModelTranslator
                .ToAchievementMissionViewModel(result.AchievementResultModel);
            ViewController.SetViewModel(viewModel);
            MissionMainViewControl.SetBadgeVisible(
                MissionType.Achievement,
                viewModel.IsReceivableRewardExist());
            MissionMainViewControl.SetBulkReceivable(viewModel.IsReceivableRewardExist());
        }
    }
}
