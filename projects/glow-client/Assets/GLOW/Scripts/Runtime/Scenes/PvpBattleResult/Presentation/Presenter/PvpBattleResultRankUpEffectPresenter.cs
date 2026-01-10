using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.Presenter
{
    public class PvpBattleResultRankUpEffectPresenter : IPvpBattleResultRankUpEffectViewDelegate
    {
        [Inject] PvpBattleResultRankUpEffectViewController ViewController { get; }
        [Inject] PvpBattleResultRankUpEffectViewController.Argument Argument { get; }
        
        CancellationToken PvpResultUpEffectCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _isRankUpEffectAnimationCompleted = false;
        bool _isFadeInAnimationCompleted = false;
        CancellationTokenSource _rankUpEffectAnimationCancellationTokenSource = new ();
        CancellationTokenSource _fadeInAnimationCancellationTokenSource = new ();
        
        void IPvpBattleResultRankUpEffectViewDelegate.OnViewDidAppear()
        {
            ViewController.SetupRank(Argument.RankType, Argument.RankLevel);
            
            DoAsync.Invoke(PvpResultUpEffectCancellationToken, async cancellationToken =>
            {
                ViewController.HideCloseButton();
                ViewController.ShowSkipButton();
                
                await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);
                
                await PlayRankUpEffectAnimation(cancellationToken);

                await PlayFadeInAnimation(Argument.RankLevel, cancellationToken);
                
                ViewController.HideSkipButton();
                ViewController.ShowCloseButton();
                _isRankUpEffectAnimationCompleted = true;
                _isFadeInAnimationCompleted = true;
            });
        }

        void IPvpBattleResultRankUpEffectViewDelegate.OnUnloadView()
        {
            _rankUpEffectAnimationCancellationTokenSource?.Dispose();
            _fadeInAnimationCancellationTokenSource?.Dispose();
        }

        void IPvpBattleResultRankUpEffectViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IPvpBattleResultRankUpEffectViewDelegate.OnSkipButtonTapped()
        {
            if (!_isRankUpEffectAnimationCompleted)
            {
                _rankUpEffectAnimationCancellationTokenSource.Cancel();
                return;
            }
            
            if (!_isFadeInAnimationCompleted)
            {
                _fadeInAnimationCancellationTokenSource.Cancel();
            }
        }
        
        async UniTask PlayRankUpEffectAnimation(CancellationToken cancellationToken)
        {
            _rankUpEffectAnimationCancellationTokenSource?.Cancel();
            _rankUpEffectAnimationCancellationTokenSource?.Dispose();
            
            _rankUpEffectAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var rankUpEffectAnimationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, 
                _rankUpEffectAnimationCancellationTokenSource.Token);
            
            var rankUpEffectAnimationCancellationToken = rankUpEffectAnimationCancellationTokenSource.Token;
            
            var isRankUpEffectCanceled = await ViewController
                .PlayRankUpEffectAnimation(rankUpEffectAnimationCancellationToken)
                .SuppressCancellationThrow();
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);
            
            cancellationToken.ThrowIfCancellationRequested();
            
            if (isRankUpEffectCanceled)
            {
                ViewController.SkipRankUpEffectAnimation();
            }
            
            _isRankUpEffectAnimationCompleted = true;
        }
        
        async UniTask PlayFadeInAnimation(PvpRankLevel rankLevel, CancellationToken cancellationToken)
        {
            _fadeInAnimationCancellationTokenSource?.Cancel();
            _fadeInAnimationCancellationTokenSource?.Dispose();
            
            _fadeInAnimationCancellationTokenSource = new CancellationTokenSource();
            
            using var rankUpEffectAnimationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, 
                _fadeInAnimationCancellationTokenSource.Token);
            
            var rankUpEffectAnimationCancellationToken = rankUpEffectAnimationCancellationTokenSource.Token;

            var isRankUpEffectCanceled = await ViewController
                .PlayFadeInAnimation(Argument.RankLevel, rankUpEffectAnimationCancellationToken)
                .SuppressCancellationThrow();
            
            cancellationToken.ThrowIfCancellationRequested();
            
            if (isRankUpEffectCanceled)
            {
                ViewController.SkipFadeInAnimation(rankLevel);
            }
            
            _isFadeInAnimationCompleted = true;
        }
    }
}