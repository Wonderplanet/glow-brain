using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Translator;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.ArtworkPanelMission.Domain.UseCase;
using GLOW.Scenes.ArtworkPanelMission.Presentation.Translator;
using GLOW.Scenes.ArtworkPanelMission.Presentation.View;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Domain.UseCase;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Mission.Presentation.Navigation;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Presenter
{
    public class ArtworkPanelMissionPresenter : IArtworkPanelMissionViewDelegate
    {
        [Inject] ArtworkPanelMissionViewController ViewController { get; }
        [Inject] ArtworkPanelMissionViewController.Argument Argument { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] ReceiveArtworkPanelMissionRewardUseCase ReceiveArtworkPanelMissionRewardUseCase { get; }
        [Inject] ShowReceivedArtworkPanelUseCase ShowReceivedArtworkPanelUseCase { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }
        [Inject] ShowArtworkDetailUseCase ShowArtworkDetailUseCase { get; }
        [Inject] IMissionNavigator MissionNavigator { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }

        CancellationToken ArtworkPanelMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        readonly CancellationTokenSource _artworkFragmentAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _artworkCompleteAnimationCancellationTokenSource = new ();

        bool _isArtworkFragmentAnimationCompleted;
        bool _isArtworkCompleteAnimationCompleted;
        bool _isAnimationEnded;

        void IArtworkPanelMissionViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUpArtworkPanelComponent(Argument.ViewModel);
            ViewController.SetUpMissionList(Argument.ViewModel.ArtworkPanelMissionFetchResultViewModel);
        }

        void IArtworkPanelMissionViewDelegate.OnCloseButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IArtworkPanelMissionViewDelegate.OnBulkReceiveButtonTapped()
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                cancellationToken => BulkReceiveReward(
                    MasterDataId.Empty,
                    true,
                    cancellationToken),
                OnClosedCommonReceive);
        }

        void IArtworkPanelMissionViewDelegate.OnReceiveButtonTapped(MasterDataId mstMissionId)
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                cancellationToken => BulkReceiveReward(
                    mstMissionId,
                    false,
                    cancellationToken),
                OnClosedCommonReceive);
        }

        void IArtworkPanelMissionViewDelegate.OnChallengeButtonTapped(DestinationScene destinationScene)
        {
            if (!EventOpenCheckUseCase.IsOpenEvent(Argument.ViewModel.MstEventId))
            {
                MessageViewUtil.ShowMessageWithClose(
                    "確認",
                    "開催期間外のため挑戦できません。",
                    null,
                    () =>
                    {
                        HomeViewNavigation.TryPop();
                    });
                return;
            }

            var destinationSceneEnum = destinationScene.TryToEnum();
            switch (destinationSceneEnum)
            {
                case DestinationSceneEnum.Home:
                case DestinationSceneEnum.StageSelect:
                    TransitHomeTop(null);
                    break;
                case DestinationSceneEnum.QuestSelect:
                    TransitHomeTop(() => { MissionNavigator.ShowHomeQuestSelectView(); });
                    break;
                case DestinationSceneEnum.UnitList:
                    TransitHomeTop(() => { MissionNavigator.ShowUnitListView(); });
                    break;
                case DestinationSceneEnum.IdleIncentive:
                    TransitHomeTop(() => { MissionNavigator.ShowIdleIncentiveTopView(); });
                    break;
                case DestinationSceneEnum.OutpostEnhance:
                    TransitHomeTop(() => { MissionNavigator.ShowOutpostEnhanceView(); });
                    break;
                case DestinationSceneEnum.Gacha:
                    TransitHomeTop(() => { MissionNavigator.ShowGachaView(MasterDataId.Empty); });
                    break;
                case DestinationSceneEnum.Event:
                    if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Content)
                    {
                        // イベントから遷移した場合は戻ると同じ挙動にする
                        HomeViewNavigation.TryPop();
                    }
                    else
                    {
                        TransitHomeTop(() => { MissionNavigator.ShowEventQuestSelectView(Argument.ViewModel.MstEventId); });
                    }
                    break;
                case DestinationSceneEnum.Pvp:
                    TransitHomeTop(() => { MissionNavigator.ShowPvpTopView(); });
                    break;
                case DestinationSceneEnum.Empty:
                    Toast.MakeText("設定されておりません。").Show();
                    break;
                default:
                    Toast.MakeText("実装されていません。").Show();
                    break;
            }
        }

        void IArtworkPanelMissionViewDelegate.OnRewardIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void IArtworkPanelMissionViewDelegate.OnArtworkIconTapped(PlayerResourceIconViewModel viewModel)
        {
            TransitToArtworkDetail(viewModel);
        }

        void IArtworkPanelMissionViewDelegate.OnSkipButtonTapped()
        {
            SkipAnimation();
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> BulkReceiveReward(
            MasterDataId mstMissionId,
            bool isBulkReceive,
            CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var result  = await ReceiveArtworkPanelMissionRewardUseCase.ReceiveMissionReward(
                    cancellationToken,
                    mstMissionId,
                    Argument.ViewModel.MstArtworkPanelMissionId,
                    isBulkReceive);

                HomeHeaderDelegate.UpdateStatus();

                if (result.IsEmpty()) return new List<CommonReceiveResourceViewModel>();

                // ミッション更新
                var fetchResultViewModel = ArtworkPanelMissionFetchResultViewModelTranslator.ToViewModel(
                    result.ArtworkPanelMissionFetchResultModel);
                ViewController.SetUpMissionList(fetchResultViewModel);

                // 報酬受取結果表示
                var receivedPlayerResourceIconViewModels = result.CommonReceiveResourceModels
                    .Select(model => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(model))
                    .ToList();

                return PlayerResourceMerger.MergeCommonReceiveResourceModel(receivedPlayerResourceIconViewModels);
            });

            return receiveRewardTask;
        }

        void OnClosedCommonReceive()
        {
            DoAsync.Invoke(ArtworkPanelMissionCancellationToken, async cancellationToken =>
            {
                using (ViewController.ViewTapGuard())
                {
                    await PlayArtworkAnimation(cancellationToken);
                    await HomeHeaderDelegate.PlayExpGaugeAnimationAsync(cancellationToken);
                }
            });
        }

        async UniTask PlayArtworkAnimation(CancellationToken cancellationToken)
        {
            var receivedArtworkPanelInfo = ShowReceivedArtworkPanelUseCase.GetAndClearReceivedArtworkPanelInfo();

            // 原画のかけら獲得アニメーション再生
            var artworkFragmentAcquisitionViewModels = ArtworkFragmentAcquisitionViewModelTranslator
                .ToTranslate(receivedArtworkPanelInfo);
            var artworkFragmentAcquisitionViewModel = artworkFragmentAcquisitionViewModels.FirstOrDefault(
                ArtworkFragmentAcquisitionViewModel.Empty);
            if (artworkFragmentAcquisitionViewModel.IsEmpty()) return;

            // アニメーション状態初期化
            _isArtworkFragmentAnimationCompleted = false;

            await PlayArtworkFragmentAnimation(artworkFragmentAcquisitionViewModel, cancellationToken);
            await PlayArtworkCompleteAnimation(artworkFragmentAcquisitionViewModel, cancellationToken);
            EndAnimation();
        }

        async UniTask PlayArtworkFragmentAnimation(
            ArtworkFragmentAcquisitionViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var fragmentAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _artworkFragmentAnimationCancellationTokenSource.Token).Token;

            var fragmentAnimationCanceled = await
                ViewController.PlayArtworkFragmentAnimation(
                        viewModel.AcquiredArtworkFragmentIds,
                        fragmentAnimationCancellationToken)
                    .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (fragmentAnimationCanceled)
            {
                _isArtworkFragmentAnimationCompleted = true;
                ViewController.SkipArtworkFragmentAnimation(viewModel.AcquiredArtworkFragmentIds);
            }

            _isArtworkFragmentAnimationCompleted = true;
        }

        async UniTask PlayArtworkCompleteAnimation(
            ArtworkFragmentAcquisitionViewModel viewModel,
            CancellationToken cancellationToken)
        {
            if (!viewModel.IsCompleted)
            {
                return;
            }

            var completeAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _artworkCompleteAnimationCancellationTokenSource.Token).Token;

            var completeAnimationCanceled = await ViewController.PlayArtworkCompleteAnimation(
                    viewModel.AddHp,
                    completeAnimationCancellationToken)
                .SuppressCancellationThrow();
            cancellationToken.ThrowIfCancellationRequested();

            if (completeAnimationCanceled)
            {
                _isArtworkCompleteAnimationCompleted = true;
                ViewController.SkipArtworkCompleteAnimation();
            }

            _isArtworkCompleteAnimationCompleted = true;
        }

        void SkipAnimation()
        {
            if (!_isArtworkFragmentAnimationCompleted && !_isArtworkCompleteAnimationCompleted)
            {
                _artworkFragmentAnimationCancellationTokenSource?.Cancel();
            }
            else if (_isArtworkFragmentAnimationCompleted && !_isArtworkCompleteAnimationCompleted)
            {
                _artworkCompleteAnimationCancellationTokenSource?.Cancel();
            }
        }

        void EndAnimation()
        {
            _isAnimationEnded = true;
        }

        void TransitHomeTop(Action onCompleted)
        {
            if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeViewNavigation.TryPopToRoot(completion: onCompleted);
            }
            else
            {
                HomeViewNavigation.Switch(HomeContentTypes.Main, completion: onCompleted);
            }
        }

        void TransitToArtworkDetail(PlayerResourceIconViewModel viewModel)
        {
            var artworkId = ShowArtworkDetailUseCase.GetArtworkIdOfArtworkFragment(viewModel.Id);
            var artworkList = new List<MasterDataId>() { artworkId };

            var argument = new ArtworkEnhanceViewController.Argument(artworkId, artworkList);
            var viewController = ViewFactory.Create<ArtworkEnhanceViewController, ArtworkEnhanceViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }
    }
}
