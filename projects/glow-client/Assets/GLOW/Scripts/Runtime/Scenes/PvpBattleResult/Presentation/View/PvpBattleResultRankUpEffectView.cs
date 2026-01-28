using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public class PvpBattleResultRankUpEffectView : UIView
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

        public async UniTask PlayRankUpEffectAnimation(CancellationToken cancellationToken)
        { 
            await _animationController.PlayAnimation(cancellationToken);
        }
        
        public void SkipAnimation()
        { 
            _animationController.Skip();
        }

        public void SetupRankIcon(PvpRankClassType rankType)
        {
            _rankingRankIcon.SetupRankType(rankType);
        }

        public async UniTask PlayFadeInRankIcon(PvpRankLevel scoreRankLevel, CancellationToken cancellationToken)
        {
            _rankingRankIconCanvasGroup.alpha = 0.0f;
            _rankingRankIcon.IsVisible = true;
            _rankingRankIcon.PlayRankTierAnimation(scoreRankLevel);
            await _rankingRankIconCanvasGroup
                .DOFade(1.0f, 0.2f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);
        }

        public void SkipFadeInRankIcon(PvpRankLevel scoreRankLevel)
        {
            _rankingRankIcon.IsVisible = true;
            _rankingRankIcon.PlayRankTierAnimation(scoreRankLevel);
            _rankingRankIconCanvasGroup.alpha = 1.0f;
        }

        public void SetupRankText(PvpRankClassType rankType, PvpRankLevel rankLevel)
        {
            _rankLabel.SetText("{0}に到達しました。", rankType.ToDisplayStringWithRankLevel(rankLevel));
        }

        public async UniTask PlayFadeInRankLabel(CancellationToken cancellationToken)
        {
            _rankLabel.IsVisible = true;
            _rankTextCanvasGroup.alpha = 0.0f;

            await _rankTextCanvasGroup.
                DOFade(1.0f, 0.2f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);
        }

        public void ShowRankLabel()
        {
            _rankLabel.IsVisible = true;
            _rankTextCanvasGroup.alpha = 1.0f;
        }

        public async UniTask PlayFadeInCloseLabel(CancellationToken cancellationToken)
        {
            _tapLabelCanvasGroup.alpha = 0.0f;
            _closeLabel.IsVisible = true;
            await _tapLabelCanvasGroup
                .DOFade(1.0f, 0.2f)
                .SetEase(Ease.Linear)
                .WithCancellation(cancellationToken);
        }

        public void ShowCloseLabel()
        {
            _tapLabelCanvasGroup.alpha = 1.0f;
            _closeLabel.IsVisible = true;
        }

        public void ShowSkipButton()
        {
            _skipScreenButton.IsVisible = true;
        }

        public void HideSkipButton()
        {
            _skipScreenButton.IsVisible = false;
        }

        public void ShowCloseButton()
        {
            _closeScreenButton.IsVisible = true;
        }

        public void HideCloseButton()
        {
            _closeScreenButton.IsVisible = false;
        }
    }
}