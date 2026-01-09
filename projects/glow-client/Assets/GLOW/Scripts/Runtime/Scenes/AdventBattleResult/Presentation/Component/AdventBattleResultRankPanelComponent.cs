using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AdventBattleResult.Presentation.ValueObject;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleResult.Presentation.Component
{
    public class AdventBattleResultRankPanelComponent : UIObject
    {
        [SerializeField] RankingRankIcon _rankIcon;
        [SerializeField] UIImage _rankProgressGaugeImage;
        [SerializeField] UIText _rankNameText;
        [SerializeField] UIText _scoreText;
        [SerializeField] AnimationPlayer _rankPanelAnimation;
        [SerializeField] Animator _rankBackEffectAnimator;
        [SerializeField] UIObject _rankBackEffectObject;

        const string RankBackEffectInAnimationName = "FlashIn";

        const float TotalScoreCountAnimationDuration = 0.4f;

        public void SetupRankIcon(
            RankType rankType,
            AdventBattleScoreRankLevel rankLevel,
            AdventBattleScore needRankUpScore,
            AdventBattleResultRankAnimationGaugeRate preGaugeRate)
        {
            _rankBackEffectObject.Hidden = true;
            _rankIcon.SetupRankType(rankType);
            _rankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
            _rankNameText.SetText(rankType.ToDisplayString());
            _rankProgressGaugeImage.Image.fillAmount = preGaugeRate.Value;
            
            var displayNeedRankUpScore = needRankUpScore.IsZero() 
                ? AdventBattleScore.Empty 
                : needRankUpScore;
            _scoreText.SetText(displayNeedRankUpScore.ToDisplayString());
        }

        public async UniTask PlayRankPanelAnimation(CancellationToken cancellationToken)
        {
            await _rankPanelAnimation.PlayAsync(cancellationToken);
            _rankBackEffectObject.Hidden = false;
            _rankBackEffectAnimator.Play(RankBackEffectInAnimationName);
        }

        public async UniTask PlayScoreCountAnimation(
            AdventBattleScore beforeScore,
            AdventBattleScore afterScore,
            AdventBattleScore targetRankLowerRequiredScore,
            bool isMaxRankLevel,
            CancellationToken cancellationToken)
        {
            var needScore = targetRankLowerRequiredScore - beforeScore;
            if (needScore < AdventBattleScore.Zero)
            {
                // ランクアップに必要なスコアが0未満の場合は0にする
                needScore = AdventBattleScore.Zero;
            }
            _scoreText.SetText(needScore.ToDisplayString());

            var currentAnimationScore = beforeScore.Value;
            await DOTween.To(
                    () => currentAnimationScore,
                    x => currentAnimationScore = x,
                    afterScore.Value,
                    TotalScoreCountAnimationDuration)
                .SetEase(Ease.Linear)
                .OnUpdate(() =>
                {
                    var subScore = targetRankLowerRequiredScore.Value - currentAnimationScore;
                    if (subScore < AdventBattleScore.Zero.Value)
                    {
                        // ランクアップに必要なスコアが0未満の場合は0にする
                        subScore = AdventBattleScore.Zero.Value;
                    }
                    _scoreText.SetText(new AdventBattleScore(subScore).ToDisplayString());
                })
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);
            
            if (isMaxRankLevel)
            {
                // 最大ランクレベルに到達した場合、次のランクの必要スコアがないため、表示を---,---,---にする
                _scoreText.SetText(AdventBattleScore.Empty.ToDisplayString());
            }
        }

        public async UniTask PlayScoreGaugeAnimation(
            AdventBattleResultRankAnimationGaugeRate afterGaugeRate,
            bool isAchievedRank,
            bool isMaxRankLevel,
            CancellationToken cancellationToken)
        {
            await _rankProgressGaugeImage.Image.DOFillAmount(afterGaugeRate.Value, TotalScoreCountAnimationDuration)
                .SetEase(Ease.Linear)
                .OnComplete(() =>
                {
                    if (isAchievedRank)
                    {
                        // 最大ランクレベルに到達した場合、次のランクの必要スコアがないためゲージを1にする
                        _rankProgressGaugeImage.Image.fillAmount = isMaxRankLevel ? 1f : 0f;
                    }
                })
                .WithCancellation(cancellationToken);
        }

        public async UniTask PlayRankChangeAnimation(
            AdventBattleScoreRankLevel nextRankLevel,
            CancellationToken cancellationToken)
        {
            await _rankIcon.PlayRankTierUpAnimation(nextRankLevel, cancellationToken);
        }

        public async UniTask PlayChangeRankIcon(
            RankType rankType,
            CancellationToken cancellationToken)
        {
            await _rankIcon.PlayRankTypeAnimation(
                rankType,
                cancellationToken);
            _rankBackEffectAnimator.Play(RankBackEffectInAnimationName);
        }

        public void SetupRankName(
            RankType rankType)
        {
            _rankNameText.SetText(rankType.ToDisplayString());
        }

        public void SkipRankPanelAnimation()
        {
            _rankBackEffectObject.Hidden = false;
            _rankPanelAnimation.Skip();
        }
    }
}
