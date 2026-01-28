using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using WonderPlanet.UniTaskSupporter;
using Zenject;
namespace GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Presenters
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    ///  　44-4-3_ランキング結果表示ダイアログ
    /// 　　　44-4-3-1_ランキング結果表示（協力バトル）ダイアログ
    /// </summary>
    public class AdventBattleRaidRankingResultPresenter : IAdventBattleRaidRankingResultViewDelegate
    {
        [Inject] AdventBattleRaidRankingResultViewController ViewController { get; }
        [Inject] AdventBattleRaidRankingResultViewController.Argument Argument { get; }

        CancellationToken AdventBattleRankingResultCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _isSlideInAnimationCompleted = false;
        bool _isEnemyIconAnimationCompleted = false;
        bool _isRewardAnimationCompleted = false;
        readonly CancellationTokenSource _slideInAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _enemyIconAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _rewardAnimationCancellationTokenSource = new ();

        public void OnViewDidLoad()
        {
            ViewController.Setup(Argument.AdventBattleRankingResultViewModel);
        }

        public void OnViewDidAppear()
        {
            DoAsync.Invoke(AdventBattleRankingResultCancellationToken, async cancellationToken =>
            {
                await PlaySlideInAnimation(cancellationToken);
                await PlayEnemyIconAnimation(cancellationToken);
                PlayEnemyLoopAnimation();
                await PlayRewardAnimation(Argument.AdventBattleRankingResultViewModel, cancellationToken);

                _isSlideInAnimationCompleted = true;
                _isEnemyIconAnimationCompleted = true;
                _isRewardAnimationCompleted = true;
            });
        }

        async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            var slideInAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _slideInAnimationCancellationTokenSource.Token).Token;

            var isSlideInCanceled = await ViewController.PlaySlideInAnimation(slideInAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isSlideInCanceled)
            {
                ViewController.SkipSlideInAnimation();
            }

            _isSlideInAnimationCompleted = true;
        }

        async UniTask PlayEnemyIconAnimation(CancellationToken cancellationToken)
        {
            var enemyIconAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _enemyIconAnimationCancellationTokenSource.Token).Token;

            var isEnemyIconCanceled = await ViewController.PlayEnemyIconAnimation(enemyIconAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isEnemyIconCanceled)
            {
                ViewController.SkipEnemyIconAnimation();
            }

            _isEnemyIconAnimationCompleted = true;
        }

        void PlayEnemyLoopAnimation()
        {
            ViewController.PlayEnemyLoopAnimation();
        }

        async UniTask PlayRewardAnimation(AdventBattleRankingResultViewModel viewModel, CancellationToken cancellationToken)
        {
            var resultRewardAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _rewardAnimationCancellationTokenSource.Token).Token;
            
            var isRewardAnimationCanceled = await ViewController.PlayRewardAnimation(viewModel, resultRewardAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isRewardAnimationCanceled)
            {
                ViewController.SkipRewardAnimation(viewModel);
            }

            _isRewardAnimationCompleted = true;
        }

        public void OnUnloadView()
        {
            _slideInAnimationCancellationTokenSource?.Dispose();
            _enemyIconAnimationCancellationTokenSource?.Dispose();
            _rewardAnimationCancellationTokenSource?.Dispose();
        }

        public void OnViewTapped()
        {
            if (!_isSlideInAnimationCompleted)
            {
                _slideInAnimationCancellationTokenSource.Cancel();
                _isSlideInAnimationCompleted = true;
                return;
            }
            if (!_isEnemyIconAnimationCompleted)
            {
                _enemyIconAnimationCancellationTokenSource.Cancel();
                _isEnemyIconAnimationCompleted = true;
                return;
            }
            if (!_isRewardAnimationCompleted)
            {
                _rewardAnimationCancellationTokenSource.Cancel();
                _isRewardAnimationCompleted = true;
                return;
            }

            if (_isRewardAnimationCompleted)
            {
                Argument.OnCloseView?.Invoke();
                ViewController.Dismiss();
            }
        }
    }
}
