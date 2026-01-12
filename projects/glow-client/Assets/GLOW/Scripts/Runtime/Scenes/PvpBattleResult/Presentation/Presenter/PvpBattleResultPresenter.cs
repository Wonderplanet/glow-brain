using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.Presenter
{
    public class PvpBattleResultPresenter : IPvpBattleResultViewDelegate
    {
        [Inject] PvpBattleResultViewController ViewController { get; }
        [Inject] PvpBattleResultViewController.Argument Argument { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IViewFactory ViewFactory { get; }
        
        CancellationToken PvpResultCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        
        bool _isDetailScoreSlideInAnimationCompleted = false;
        bool _isTotalScoreSlideInAnimationCompleted = false;
        bool _isTotalScoreAnimationCompleted = false;
        bool _isRankPanelAnimationCompleted = false;
        
        CancellationTokenSource _resultDetailPointSlideInAnimationCancellationTokenSource = new ();
        CancellationTokenSource _resultTotalPointSlideInAnimationCancellationTokenSource = new ();
        CancellationTokenSource _totalPointAnimationCancellationTokenSource = new ();
        CancellationTokenSource _rankPanelAnimationCancellationTokenSource = new ();
        
        void IPvpBattleResultViewDelegate.OnViewDidAppear()
        {
            DoAsync.Invoke(PvpResultCancellationToken, async cancellationToken =>
            {
                BackgroundMusicPlayable.PlayWithCrossFade(cancellationToken, BGMAssetKeyDefinitions.BGM_victory_result, 0.2f);
                
                ViewController.ShowActionButton();
                
                await PlayDetailPointSlideInAnimation(cancellationToken);
                await PlayTotalPointSlideInAnimation(cancellationToken);
                await PlayTotalPointCountAnimation(cancellationToken);
                await PlayRankPanelAnimation(cancellationToken);
                await ShowPvpBattleRankUpEffect(cancellationToken);
                
                ViewController.HideActionButton();
                _isDetailScoreSlideInAnimationCompleted = true;
                _isTotalScoreSlideInAnimationCompleted = true;
                _isTotalScoreAnimationCompleted = true;
                _isRankPanelAnimationCompleted = true;
            });
        }

        void IPvpBattleResultViewDelegate.OnUnloadView()
        {
            _resultDetailPointSlideInAnimationCancellationTokenSource?.Dispose();
            _resultTotalPointSlideInAnimationCancellationTokenSource?.Dispose();
            _totalPointAnimationCancellationTokenSource?.Dispose();
            _rankPanelAnimationCancellationTokenSource?.Dispose();
        }

        void IPvpBattleResultViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IPvpBattleResultViewDelegate.OnActionButtonTapped()
        {
            if (!_isDetailScoreSlideInAnimationCompleted)
            {
                _resultDetailPointSlideInAnimationCancellationTokenSource.Cancel();
                return;
            }
            
            if (!_isTotalScoreSlideInAnimationCompleted)
            {
                _resultTotalPointSlideInAnimationCancellationTokenSource.Cancel();
                return;
            }
            
            if (!_isTotalScoreAnimationCompleted)
            {
                _totalPointAnimationCancellationTokenSource.Cancel();
                return;
            }
            
            if (!_isRankPanelAnimationCompleted)
            {
                _rankPanelAnimationCancellationTokenSource.Cancel();
            }
        }
        
        async UniTask PlayDetailPointSlideInAnimation(CancellationToken cancellationToken)
        {
            _resultDetailPointSlideInAnimationCancellationTokenSource?.Cancel();
            _resultDetailPointSlideInAnimationCancellationTokenSource?.Dispose();
            
            _resultDetailPointSlideInAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var resultDetailSlideInAnimationCancellationTokenSource = 
                CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, 
                    _resultDetailPointSlideInAnimationCancellationTokenSource.Token);
            
            var resultDetailSlideInAnimationCancellationToken = resultDetailSlideInAnimationCancellationTokenSource.Token;
            
            var isSlideInCanceled = await ViewController
                .PlayDetailPointSlideInAnimation(
                    resultDetailSlideInAnimationCancellationToken,
                    Argument.ViewModel.VictoryPoint,
                    Argument.ViewModel.OpponentBonusPoint,
                    Argument.ViewModel.TimeBonusPoint)
                .SuppressCancellationThrow();
            
            cancellationToken.ThrowIfCancellationRequested();
            
            if (isSlideInCanceled)
            {
                ViewController.SkipDetailPointAnimation(
                    Argument.ViewModel.VictoryPoint,
                    Argument.ViewModel.OpponentBonusPoint,
                    Argument.ViewModel.TimeBonusPoint);
            }
            
            _isDetailScoreSlideInAnimationCompleted = true;
        }
        
        async UniTask PlayTotalPointSlideInAnimation(CancellationToken cancellationToken)
        {
            _resultTotalPointSlideInAnimationCancellationTokenSource?.Cancel();
            _resultTotalPointSlideInAnimationCancellationTokenSource?.Dispose();
            
            _resultTotalPointSlideInAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var resultTotalScoreSlideInAnimationCancellationTokenSource = 
                CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, 
                    _resultTotalPointSlideInAnimationCancellationTokenSource.Token);
            
            var resultTotalScoreSlideInAnimationCancellationToken = resultTotalScoreSlideInAnimationCancellationTokenSource.Token;
            
            var isSlideInCanceled = await ViewController
                .PlayTotalPointSlideInAnimation(resultTotalScoreSlideInAnimationCancellationToken)
                .SuppressCancellationThrow();
            
            cancellationToken.ThrowIfCancellationRequested();
            
            if (isSlideInCanceled)
            {
                ViewController.SkipTotalPointSlideInAnimation();
            }
            
            _isTotalScoreSlideInAnimationCompleted = true;
        }
        
        async UniTask PlayTotalPointCountAnimation(CancellationToken cancellationToken)
        {
            _totalPointAnimationCancellationTokenSource?.Cancel();
            _totalPointAnimationCancellationTokenSource?.Dispose();
            
            _totalPointAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var totalScoreCountAnimationCancellationTokenSource = 
                CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, 
                    _totalPointAnimationCancellationTokenSource.Token);

            var totalScoreCountAnimationCancellationToken = totalScoreCountAnimationCancellationTokenSource.Token;
            
            var isTotalScoreCountCanceled = await ViewController
                .PlayTotalPointCountAnimation(
                    totalScoreCountAnimationCancellationToken,
                    Argument.ViewModel.GainedTotalPoint,
                    Argument.ViewModel.TotalPoint)
                .SuppressCancellationThrow();
            
            cancellationToken.ThrowIfCancellationRequested();

            if (isTotalScoreCountCanceled)
            {
                ViewController.SkipTotalPointCountAnimation(
                    Argument.ViewModel.GainedTotalPoint,
                    Argument.ViewModel.TotalPoint);
            }
            
            _isTotalScoreAnimationCompleted = true;
        }
        
        async UniTask PlayRankPanelAnimation(CancellationToken cancellationToken)
        {
            _rankPanelAnimationCancellationTokenSource?.Cancel();
            _rankPanelAnimationCancellationTokenSource?.Dispose();
            
            _rankPanelAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var rankPanelAnimationCancellationTokenSource = 
                CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, 
                    _rankPanelAnimationCancellationTokenSource.Token);
            
            var rankPanelAnimationCancellationToken = rankPanelAnimationCancellationTokenSource.Token;
            
            ViewController.HideCloseText();
            var isRankPanelAnimationCanceled = await ViewController
                .PlayRankPanelAnimation(rankPanelAnimationCancellationToken, Argument.ViewModel)
                .SuppressCancellationThrow();
            
            cancellationToken.ThrowIfCancellationRequested();
            
            if (isRankPanelAnimationCanceled)
            {
                ViewController.SkipRankPanelAnimation(Argument.ViewModel);
            }
            
            _isRankPanelAnimationCompleted = true;
        }
        
        async UniTask ShowPvpBattleRankUpEffect(CancellationToken cancellationToken)
        {
            if (!Argument.ViewModel.IsRankOrRankLevelUp())
            {
                return;
            }
            
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var lastAchievedRankAndLevel = Argument.ViewModel.LastAchievedRankAndLevel();
            var argument = new PvpBattleResultRankUpEffectViewController.Argument(
                lastAchievedRankAndLevel.rankType, 
                lastAchievedRankAndLevel.rankLevel);
            var controller = ViewFactory.Create<
                PvpBattleResultRankUpEffectViewController, 
                PvpBattleResultRankUpEffectViewController.Argument>(argument);
            controller.OnCloseCompletion = () =>
            {
                completionSource.TrySetResult();
            };
            ViewController.PresentModally(controller);
            
            await completionSource.Task;
        }
    }
}