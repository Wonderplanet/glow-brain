using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultPresenter : IAdventBattleResultViewDelegate
    {
        [Inject] AdventBattleResultViewController ViewController { get; }
        [Inject] AdventBattleResultViewController.Argument Argument { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IUserLevelUpResultViewFacade UserLevelUpResultViewFacade { get; }

        CancellationToken AdventBattleResultCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _isDetailScoreSlideInAnimationCompleted = false;
        bool _isTotalScoreSlideInAnimationCompleted = false;
        bool _isTotalScoreAnimationCompleted = false;
        bool _isResultRewardAnimationCompleted = false;
        bool _isRankPanelAnimationCompleted = false;

        readonly CancellationTokenSource _resultDetailScoreSlideInAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _resultTotalScoreSlideInAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _totalScoreAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _resultRewardAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _rankPanelAnimationCancellationTokenSource = new ();

        void IAdventBattleResultViewDelegate.OnViewDidAppear()
        {
            ViewController.SetUpEventCampaignBalloon(Argument.ViewModel.RemainingEventCampaignTimeSpan);
            ViewController.SetupRetryButton(Argument.ViewModel.IsRetryAvailable);

            DoAsync.Invoke(AdventBattleResultCancellationToken, async cancellationToken =>
            {
                BackgroundMusicPlayable.PlayWithCrossFade(cancellationToken, BGMAssetKeyDefinitions.BGM_victory_result, 0.2f);
                ViewController.ShowActionButton();

                await PlayDetailScoreSlideInAnimation(Argument.ViewModel, cancellationToken);
                await PlayTotalScoreSlideInAnimation(cancellationToken);
                await PlayTotalScoreCountAnimation(Argument.ViewModel, cancellationToken);
                await PlayRankPanelAnimation(Argument.ViewModel.AdventBattleResultScoreViewModel, cancellationToken);
                await ShowAdventBattleRankUpEffect(cancellationToken);
                await ShowReceivedUnits(Argument.ViewModel.AcquiredPlayerResources, cancellationToken);
                await PlayRewardAnimation(Argument.ViewModel, cancellationToken);

                ViewController.HideActionButton();
                _isDetailScoreSlideInAnimationCompleted = true;
                _isTotalScoreSlideInAnimationCompleted = true;
                _isTotalScoreAnimationCompleted = true;
                _isResultRewardAnimationCompleted = true;
                _isRankPanelAnimationCompleted = true;
                
                // アニメーション完了後に再挑戦ボタンを有効化
                ViewController.SetActiveRetryButton(Argument.ViewModel.IsRetryAvailable);
                
                // ユーザーレベルアップの結果を表示
                UserLevelUpResultViewFacade.Show(Argument.ViewModel.UserLevelUpResult);
            });
        }

        void IAdventBattleResultViewDelegate.OnUnloadView()
        {
            _resultDetailScoreSlideInAnimationCancellationTokenSource?.Dispose();
            _resultTotalScoreSlideInAnimationCancellationTokenSource?.Dispose();
            _totalScoreAnimationCancellationTokenSource?.Dispose();
            _resultRewardAnimationCancellationTokenSource?.Dispose();
            _rankPanelAnimationCancellationTokenSource?.Dispose();
        }

        void IAdventBattleResultViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IAdventBattleResultViewDelegate.OnActionButtonTapped()
        {
            // 詳細スコアがスライドインするところまでスキップ
            if (!_isDetailScoreSlideInAnimationCompleted)
            {
                _resultDetailScoreSlideInAnimationCancellationTokenSource.Cancel();
                return;
            }

            // トータススコアがスライドインするところまでスキップ
            if (!_isTotalScoreSlideInAnimationCompleted)
            {
                _resultTotalScoreSlideInAnimationCancellationTokenSource.Cancel();
                return;
            }

            // スコア表示までスキップ
            if (!_isTotalScoreAnimationCompleted)
            {
                _totalScoreAnimationCancellationTokenSource.Cancel();
                return;
            }

            // ランク表示までスキップ
            if (!_isRankPanelAnimationCompleted)
            {
                _rankPanelAnimationCancellationTokenSource.Cancel();
                return;
            }

            // 報酬表示までスキップ
            if (!_isResultRewardAnimationCompleted)
            {
                _resultRewardAnimationCancellationTokenSource.Cancel();
            }
        }
        
        async void IAdventBattleResultViewDelegate.OnRetryButtonTapped()
        {
            // スタミナブーストやスタミナ不足の確認を含むリトライ判定
            if (ViewController.OnRetryAction != null)
            {
                await ViewController.OnRetryAction.Invoke();
            }
        }

        void IAdventBattleResultViewDelegate.OnIconSelected(PlayerResourceIconViewModel viewModel)
        {
            if (!_isDetailScoreSlideInAnimationCompleted) return;
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        async UniTask PlayDetailScoreSlideInAnimation(AdventBattleResultViewModel viewModel, CancellationToken cancellationToken)
        {
            var resultDetailSlideInAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _resultDetailScoreSlideInAnimationCancellationTokenSource.Token).Token;

            var isSlideInCanceled = await ViewController.PlayDetailScoreSlideInAnimation(
                    viewModel.AdventBattleResultScoreViewModel.DamageScore,
                    viewModel.AdventBattleResultScoreViewModel.EnemyDefeatScore,
                    viewModel.AdventBattleResultScoreViewModel.BossEnemyDefeatScore,
                    resultDetailSlideInAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isSlideInCanceled)
            {
                ViewController.SkipDetailScoreAnimation(
                    viewModel.AdventBattleResultScoreViewModel.DamageScore,
                    viewModel.AdventBattleResultScoreViewModel.EnemyDefeatScore,
                    viewModel.AdventBattleResultScoreViewModel.BossEnemyDefeatScore);
            }

            _isDetailScoreSlideInAnimationCompleted = true;
        }

        async UniTask PlayTotalScoreSlideInAnimation(CancellationToken cancellationToken)
        {
            var resultTotalScoreSlideInAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _totalScoreAnimationCancellationTokenSource.Token).Token;

            var isSlideInCanceled = await ViewController.PlayTotalScoreSlideInAnimation(
                    resultTotalScoreSlideInAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isSlideInCanceled)
            {
                ViewController.SkipTotalScoreSlideInAnimation();
            }

            _isTotalScoreSlideInAnimationCompleted = true;
        }

        async UniTask PlayTotalScoreCountAnimation(AdventBattleResultViewModel viewModel, CancellationToken cancellationToken)
        {
            var totalScoreCountAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _totalScoreAnimationCancellationTokenSource.Token).Token;

            var isTotalScoreCountCanceled = await ViewController.PlayTotalScoreCountAnimation(
                    viewModel,
                    totalScoreCountAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isTotalScoreCountCanceled)
            {
                ViewController.SkipTotalScoreCountAnimation(viewModel);
            }

            _isTotalScoreAnimationCompleted = true;
        }

        async UniTask PlayRankPanelAnimation(AdventBattleResultScoreViewModel viewModel, CancellationToken cancellationToken)
        {
            var rankPanelAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _rankPanelAnimationCancellationTokenSource.Token).Token;

            ViewController.HideCloseText();
            var isRankPanelAnimationCanceled = await ViewController.PlayRankPanelAnimation(
                    viewModel,
                    rankPanelAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isRankPanelAnimationCanceled)
            {
                ViewController.SkipRankPanelAnimation(viewModel);
            }

            _isRankPanelAnimationCompleted = true;
        }

        async UniTask PlayRewardAnimation(AdventBattleResultViewModel viewModel, CancellationToken cancellationToken)
        {
            var resultRewardAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _resultRewardAnimationCancellationTokenSource.Token).Token;

            var isRewardAnimationCanceled = await ViewController.PlayRewardAnimation(
                    viewModel,
                    resultRewardAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isRewardAnimationCanceled)
            {
                ViewController.SkipRewardAnimation(viewModel);
            }

            _isResultRewardAnimationCompleted = true;
        }

        async UniTask ShowAdventBattleRankUpEffect(CancellationToken cancellationToken)
        {
            if (!Argument.ViewModel.AdventBattleResultScoreViewModel.IsRankOrRankLevelUp())
            {
                return;
            }

            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var lastAchievedRankAndLevel = Argument.ViewModel.AdventBattleResultScoreViewModel.LastAchievedRankAndLevel();
            var argument = new AdventBattleResultRankUpEffectViewController.Argument(
                lastAchievedRankAndLevel.rankType,
                lastAchievedRankAndLevel.rankLevel,
                Argument.ViewModel.AdventBattleResultScoreViewModel.RankRewards);
            var controller = ViewFactory.Create<
                AdventBattleResultRankUpEffectViewController,
                AdventBattleResultRankUpEffectViewController.Argument>(argument);
            controller.OnCloseCompletion = () =>
            {
                completionSource.TrySetResult();
            };
            ViewController.PresentModally(controller);

            await completionSource.Task;
        }

        async UniTask ShowReceivedUnits(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            var receivedUnitIds = viewModels
                .Where(model => model.ResourceType == ResourceType.Unit)
                .Select(model => model.Id)
                .ToList();

            await UnitReceiveWireFrame.ShowReceivedUnits(
                receivedUnitIds,
                ViewController,
                cancellationToken);
        }
    }
}
