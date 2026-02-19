using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.AdventBattleMission.Domain.UseCase;
using GLOW.Scenes.AdventBattleMission.Presentation.Translator;
using GLOW.Scenes.AdventBattleMission.Presentation.View;
using GLOW.Scenes.AdventBattleMission.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-7_ミッションアイコン（専用画面表示も実装に含む）
    /// </summary>
    public class AdventBattleMissionPresenter : IAdventBattleMissionViewDelegate
    {
        [Inject] AdventBattleMissionViewController ViewController { get; }
        [Inject] ShowAdventBattleMissionListUseCase ShowAdventBattleMissionListUseCase { get; }
        [Inject] ReceiveAdventBattleMissionRewardUseCase ReceiveAdventBattleMissionRewardUseCase { get; }
        [Inject] BulkReceiveAdventBattleMissionRewardUseCase BulkReceiveAdventBattleMissionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }


        CancellationToken AdventBattleMissionCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        AdventBattleMissionViewModel _adventBattleMissionViewModel = AdventBattleMissionViewModel.Empty;

        void IAdventBattleMissionViewDelegate.OnViewWillAppear()
        {
            DoAsync.Invoke(AdventBattleMissionCancellationToken, async cancellationToken =>
            {
                // 通信段階では一括受け取りを押せなくする
                ViewController.SetBulkReceivable(false);

                _adventBattleMissionViewModel = await FetchAdventBattleMission(cancellationToken);

                // 通信段階では一括受け取りを推せるかどうかの切り替え
                ViewController.SetBulkReceivable(_adventBattleMissionViewModel.IsBulkReceivable);
                ViewController.SetMissionList(_adventBattleMissionViewModel.AdventBattleMissionCellViewModels);
                ViewController.SetIndicatorHidden(true);
            });
        }

        void IAdventBattleMissionViewDelegate.OnReceiveButtonTapped(AdventBattleMissionCellViewModel viewModel)
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                cancellationToken => ReceiveRewardSingle(viewModel, cancellationToken),
                () => { HomeHeaderDelegate.PlayExpGaugeAnimation(); });
        }

        void IAdventBattleMissionViewDelegate.OnBulkReceiveButtonTapped()
        {
            CommonReceiveWireFrame.AsyncShowReceived(
                BulkReceiveReward,
                () =>
                {
                    HomeHeaderDelegate.PlayExpGaugeAnimation();
                });
        }

        void IAdventBattleMissionViewDelegate.OnChallengeButtonTapped(DestinationScene destination, Action onTransitionCompleted)
        {
            // 降臨バトルTOP以外では表示箇所がない上、降臨バトル関連のミッションしか表示しないため、ダイアログを消すだけでよい
            ViewController.Dismiss(completion: onTransitionCompleted);
        }

        void IAdventBattleMissionViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(viewModel, ViewController);
        }

        void IAdventBattleMissionViewDelegate.OnCloseButtonTapped()
        {
            Close();
        }

        void IAdventBattleMissionViewDelegate.OnEscape()
        {
            if (!ViewController.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            ViewController.SetInteractable(false);

            SystemSoundEffectProvider.PlaySeEscape();
            Close();
        }

        async UniTask<AdventBattleMissionViewModel> FetchAdventBattleMission(CancellationToken cancellationToken)
        {
            var resultModel = await ShowAdventBattleMissionListUseCase.GetAdventBattleMissionList(cancellationToken);
            return AdventBattleMissionViewModelTranslator.ToAdventBattleMissionCellViewModels(resultModel);
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> ReceiveRewardSingle(
            AdventBattleMissionCellViewModel viewModel,
            CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var receiveReward = await ReceiveAdventBattleMissionRewardUseCase.ReceiveAdventBattleMissionReward(
                    cancellationToken,
                    viewModel.MissionType,
                    viewModel.AdventBattleMissionId);

                HomeHeaderDelegate.UpdateStatus();

                _adventBattleMissionViewModel =
                    AdventBattleMissionViewModelTranslator.ToAdventBattleMissionCellViewModels(
                        receiveReward.AdventBattleMissionFetchResultModel);
                ViewController.SetMissionList(_adventBattleMissionViewModel.AdventBattleMissionCellViewModels);
                ViewController.SetBulkReceivable(_adventBattleMissionViewModel.IsBulkReceivable);

                var rewards = receiveReward.CommonReceiveResourceModels
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(rewards);
            });

            return receiveRewardTask;
        }

        UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> BulkReceiveReward(CancellationToken cancellationToken)
        {
            var receiveRewardTask = UniTask.Create(async () =>
            {
                var receiveReward = await BulkReceiveAdventBattleMissionRewardUseCase.BulkReceiveMissionReward(
                    cancellationToken,
                    MissionType.LimitedTerm);
                HomeHeaderDelegate.UpdateStatus();

                _adventBattleMissionViewModel =
                    AdventBattleMissionViewModelTranslator.ToAdventBattleMissionCellViewModels(
                        receiveReward.AdventBattleMissionFetchResultModel);
                ViewController.SetMissionList(_adventBattleMissionViewModel.AdventBattleMissionCellViewModels);
                ViewController.SetBulkReceivable(_adventBattleMissionViewModel.IsBulkReceivable);

                var viewModel = receiveReward.CommonReceiveResourceModels
                    .Select(m =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                    .ToList();
                return PlayerResourceMerger.MergeCommonReceiveResourceModel(viewModel);
            });

            return receiveRewardTask;
        }
        
        void Close()
        {
            ViewController.AdventBattleMissionBadgeAction?.Invoke(
                _adventBattleMissionViewModel.IsBulkReceivable.ToNotificationBadge());
            
            ViewController.Dismiss();
        }
    }
}
