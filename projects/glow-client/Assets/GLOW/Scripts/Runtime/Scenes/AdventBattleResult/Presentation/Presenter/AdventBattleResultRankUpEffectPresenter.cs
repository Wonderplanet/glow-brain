using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultRankUpEffectPresenter : IAdventBattleResultRankUpEffectViewDelegate
    {
        [Inject] AdventBattleResultRankUpEffectViewController ViewController { get; }
        [Inject] AdventBattleResultRankUpEffectViewController.Argument Argument { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        CancellationToken AdventBattleResultUpEffectCancellationToken =>
            ViewController.ActualView.GetCancellationTokenOnDestroy();

        bool _isRankUpEffectAnimationCompleted = false;
        bool _isFadeInAnimationCompleted = false;
        bool _isRankRewardAnimationCompleted = false;
        readonly CancellationTokenSource _rankUpEffectAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _fadeInAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _rankRewardAnimationCancellationTokenSource = new ();

        void IAdventBattleResultRankUpEffectViewDelegate.OnViewDidAppear()
        {
            ViewController.SetupRank(Argument.RankType, Argument.RankLevel);

            DoAsync.Invoke(AdventBattleResultUpEffectCancellationToken, async cancellationToken =>
            {
                ViewController.HideCloseButton();
                ViewController.ShowSkipButton();

                await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

                await PlayRankUpEffectAnimation(cancellationToken);

                await PlayFadeInAnimation(Argument.RankLevel, cancellationToken);

                await PlayRankRewardAnimation(Argument.RankRewards, cancellationToken);

                ViewController.HideSkipButton();
                ViewController.ShowCloseButton();
                _isRankUpEffectAnimationCompleted = true;
                _isFadeInAnimationCompleted = true;
                _isRankRewardAnimationCompleted = true;
            });
        }

        void IAdventBattleResultRankUpEffectViewDelegate.OnUnloadView()
        {
            _rankUpEffectAnimationCancellationTokenSource?.Dispose();
            _fadeInAnimationCancellationTokenSource?.Dispose();
            _rankRewardAnimationCancellationTokenSource?.Dispose();
        }

        void IAdventBattleResultRankUpEffectViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IAdventBattleResultRankUpEffectViewDelegate.OnSkipButtonTapped()
        {
            if (!_isRankUpEffectAnimationCompleted)
            {
                _rankUpEffectAnimationCancellationTokenSource.Cancel();
                return;
            }

            if (!_isFadeInAnimationCompleted)
            {
                _fadeInAnimationCancellationTokenSource.Cancel();
                return;
            }

            if (!_isRankRewardAnimationCompleted)
            {
                _rankRewardAnimationCancellationTokenSource.Cancel();
            }
        }

        void IAdventBattleResultRankUpEffectViewDelegate.OnPlayerResourceIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        async UniTask PlayRankUpEffectAnimation(CancellationToken cancellationToken)
        {
            var rankUpEffectAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _rankUpEffectAnimationCancellationTokenSource.Token).Token;

            var isRankUpEffectCanceled = await ViewController.PlayRankUpEffectAnimation(
                    rankUpEffectAnimationCancellationToken)
                .SuppressCancellationThrow();
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);

            cancellationToken.ThrowIfCancellationRequested();

            if (isRankUpEffectCanceled)
            {
                ViewController.SkipRankUpEffectAnimation();
            }

            _isRankUpEffectAnimationCompleted = true;
        }

        async UniTask PlayFadeInAnimation(AdventBattleScoreRankLevel rankLevel, CancellationToken cancellationToken)
        {
            var rankUpEffectAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _fadeInAnimationCancellationTokenSource.Token).Token;

            var isRankUpEffectCanceled = await ViewController.PlayFadeInAnimation(
                    Argument.RankLevel,
                    rankUpEffectAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isRankUpEffectCanceled)
            {
                ViewController.SkipFadeInAnimation(rankLevel);
            }

            _isFadeInAnimationCompleted = true;
        }

        async UniTask PlayRankRewardAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rankRewards,
            CancellationToken cancellationToken)
        {
            var rankRewardAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _rankRewardAnimationCancellationTokenSource.Token).Token;

            var isRankRewardAnimationCanceled = await ViewController.PlayRankRewardAnimation(
                    rankRewards,
                    rankRewardAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (isRankRewardAnimationCanceled)
            {
                ViewController.SkipRankRewardAnimation(rankRewards);
            }

            _isRankRewardAnimationCompleted = true;
        }
    }
}
