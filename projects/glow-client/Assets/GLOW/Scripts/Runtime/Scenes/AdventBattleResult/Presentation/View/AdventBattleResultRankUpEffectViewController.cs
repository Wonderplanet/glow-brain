using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultRankUpEffectViewController :
        UIViewController<AdventBattleResultRankUpEffectView>,
        IEscapeResponder
    {
        public record Argument(
            RankType RankType,
            AdventBattleScoreRankLevel RankLevel,
            IReadOnlyList<PlayerResourceIconViewModel> RankRewards);

        public Action OnCloseCompletion { get; set; }

        [Inject] IAdventBattleResultRankUpEffectViewDelegate ViewDelegate { get; }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
            ActualView.OnPlayerResourceIconTapped = ViewDelegate.OnPlayerResourceIconTapped;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void UnloadView()
        {
            base.UnloadView();
            ViewDelegate.OnUnloadView();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetupRank(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            ActualView.SetupRankIcon(rankType);
            ActualView.SetupRankText(rankType, rankLevel);
        }

        public async UniTask PlayRankUpEffectAnimation(
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRankUpEffectAnimation(cancellationToken);
        }

        public async UniTask PlayFadeInAnimation(
            AdventBattleScoreRankLevel scoreRankLevel,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayFadeInRankIcon(scoreRankLevel, cancellationToken);

            await ActualView.PlayFadeInRankLabel(cancellationToken);
        }

        public async UniTask PlayRankRewardAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rankRewards,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRewardLabelComponent(cancellationToken);

            await ActualView.PlayRankRewardItemAnimation(rankRewards, cancellationToken);

            await ActualView.PlayFadeInCloseLabel(cancellationToken);
        }

        public void SkipRankUpEffectAnimation()
        {
            ActualView.SkipAnimation();
        }

        public void SkipFadeInAnimation(AdventBattleScoreRankLevel scoreRankLevel)
        {
            ActualView.SkipFadeInRankIcon(scoreRankLevel);

            ActualView.ShowRankLabel();
        }

        public void SkipRankRewardAnimation(IReadOnlyList<PlayerResourceIconViewModel> rankRewards)
        {
            ActualView.ShowRewardLabelComponent();

            ActualView.SkipRankRewardItemAnimation(rankRewards);

            ActualView.ShowCloseLabel();
        }

        public void ShowSkipButton()
        {
            ActualView.ShowSkipButton();
        }

        public void HideSkipButton()
        {
            ActualView.HideSkipButton();
        }

        public void ShowCloseButton()
        {
            ActualView.ShowCloseButton();
        }

        public void HideCloseButton()
        {
            ActualView.HideCloseButton();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
            OnCloseCompletion?.Invoke();
        }

        [UIAction]
        void OnSkipButtonTapped()
        {
            ViewDelegate.OnSkipButtonTapped();
        }

        public bool OnEscape()
        {
            if (ActualView.Hidden) return false;

            OnCloseButtonTapped();
            return true;
        }
    }
}
