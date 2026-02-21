using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10-1_降臨バトル専用バトルリザルト画面演出
    /// </summary>
    public class AdventBattleResultRankUpEffectView : UIView
    {
        [SerializeField] UserRankUpEffectAnimationController _animationController;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] CanvasGroup _rankingRankIconCanvasGroup;
        [SerializeField] UIObject _skipScreenButton;
        [SerializeField] UIObject _closeScreenButton;
        [SerializeField] UIText _rankLabel;
        [SerializeField] UIText _closeLabel;
        [SerializeField] CanvasGroup _rankTextCanvasGroup;
        [SerializeField] CanvasGroup _tapLabelCanvasGroup;
        [SerializeField] CanvasGroup _backgroundCanvasGroup;
        [SerializeField] RewardSectionLabelComponent rewardSectionLabelComponent;
        [SerializeField] RewardListComponent _rankRewardListComponent;

        public Action<PlayerResourceIconViewModel> OnPlayerResourceIconTapped
        {
            get => _rankRewardListComponent.OnPlayerResourceIconTapped;
            set => _rankRewardListComponent.OnPlayerResourceIconTapped = value;
        }

        public async UniTask PlayRankUpEffectAnimation(CancellationToken cancellationToken)
        {
            await _animationController.PlayAnimation(cancellationToken);
        }

        public void SkipAnimation()
        {
            _animationController.Skip();
        }

        public void SetupRankIcon(RankType rankType)
        {
            _rankingRankIcon.SetupRankType(rankType);
        }

        public async UniTask PlayFadeInRankIcon(AdventBattleScoreRankLevel scoreRankLevel, CancellationToken cancellationToken)
        {
            _rankingRankIconCanvasGroup.alpha = 0.0f;
            _rankingRankIcon.Hidden = false;
            _rankingRankIcon.PlayRankTierAnimation(scoreRankLevel.ToScoreRankLevel());
            await _rankingRankIconCanvasGroup.DOFade(1.0f, 0.2f).SetEase(Ease.Linear).WithCancellation(cancellationToken);
        }

        public void SkipFadeInRankIcon(AdventBattleScoreRankLevel scoreRankLevel)
        {
            _rankingRankIcon.Hidden = false;
            _rankingRankIcon.PlayRankTierAnimation(scoreRankLevel.ToScoreRankLevel());
            _rankingRankIconCanvasGroup.alpha = 1.0f;
        }

        public void SetupRankText(RankType rankType, AdventBattleScoreRankLevel scoreRankLevel)
        {
            _rankLabel.SetText(ZString.Format("{0}に到達しました。",rankType.ToDisplayStringWithRankLevel(scoreRankLevel)));
        }

        public async UniTask PlayFadeInRankLabel(CancellationToken cancellationToken)
        {
            _rankLabel.Hidden = false;
            _rankTextCanvasGroup.alpha = 0.0f;

            await _rankTextCanvasGroup.DOFade(1.0f, 0.2f).SetEase(Ease.Linear).WithCancellation(cancellationToken);
        }

        public async UniTask PlayRewardLabelComponent(CancellationToken cancellationToken)
        {
            rewardSectionLabelComponent.Hidden = false;
            await rewardSectionLabelComponent.PlayFadeIn(cancellationToken);
        }

        public void ShowRewardLabelComponent()
        {
            rewardSectionLabelComponent.Hidden = false;
            rewardSectionLabelComponent.ShowRewardLabel();
        }

        public async UniTask PlayRankRewardItemAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rankUpRewardViewModels,
            CancellationToken cancellationToken)
        {
            _rankRewardListComponent.Hidden = false;
            await _rankRewardListComponent.PlayRewardListAnimation(rankUpRewardViewModels, 2, cancellationToken);

        }

        public void SkipRankRewardItemAnimation(IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels)
        {
            _rankRewardListComponent.Hidden = false;
            _rankRewardListComponent.ShowRewardList(rewardViewModels, 2);
        }

        public void ShowRankLabel()
        {
            _rankLabel.Hidden = false;
            _rankTextCanvasGroup.alpha = 1.0f;
        }

        public async UniTask PlayFadeInCloseLabel(CancellationToken cancellationToken)
        {
            _tapLabelCanvasGroup.alpha = 0.0f;
            _closeLabel.Hidden = false;
            await _tapLabelCanvasGroup.DOFade(1.0f, 0.2f).SetEase(Ease.Linear).WithCancellation(cancellationToken);
        }

        public void ShowCloseLabel()
        {
            _tapLabelCanvasGroup.alpha = 1.0f;
            _closeLabel.Hidden = false;
        }

        public void ShowSkipButton()
        {
            _skipScreenButton.Hidden = false;
        }

        public void HideSkipButton()
        {
            _skipScreenButton.Hidden = true;
        }

        public void ShowCloseButton()
        {
            _closeScreenButton.Hidden = false;
        }

        public void HideCloseButton()
        {
            _closeScreenButton.Hidden = true;
        }
    }
}
