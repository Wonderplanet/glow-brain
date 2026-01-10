using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;
using UnityEngine;

namespace GLOW.Scenes.PvpBattleResult.Presentation.Component
{
    public class PvpBattleResultRankPanelComponent : UIObject
    {
        [SerializeField] RankingRankIcon _rankIcon;
        [SerializeField] UIImage _rankProgressGaugeImage;
        [SerializeField] UIText _rankNameText;
        [SerializeField] UIText _pointText;
        [SerializeField] AnimationPlayer _rankPanelAnimation;
        [SerializeField] Animator _rankBackEffectAnimator;
        [SerializeField] UIObject _rankBackEffectObject;
        [SerializeField] UIObject _requiredPointAreaObject;
        [SerializeField] UIObject _achievedMaxRankObject;

        const string RankBackEffectInAnimationName = "FlashIn";

        const float TotalScoreCountAnimationDuration = 0.4f;

        public void InitializeRequiredPointArea(
            PvpPoint beforePoint,
            PvpPoint targetRankLowerRequiredPoint,
            bool isTargetMaxRankLevel)
        {
            bool isAchievedBeforeMaxRank = isTargetMaxRankLevel && beforePoint >= targetRankLowerRequiredPoint;
            _requiredPointAreaObject.IsVisible = !isAchievedBeforeMaxRank;
            _achievedMaxRankObject.IsVisible = isAchievedBeforeMaxRank;
        }

        public void SetupRankIcon(
            PvpRankClassType rankType,
            PvpRankLevel rankLevel,
            PvpPoint pointToNextRank,
            PvpBattleResultRankAnimationGaugeRate prevGaugeRate)
        {
            _rankBackEffectObject.IsVisible = false;
            _rankIcon.SetupRankType(rankType);
            _rankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
            _rankNameText.SetText(rankType.ToDisplayString());
            _rankProgressGaugeImage.Image.fillAmount = prevGaugeRate.Value;
            _pointText.SetText(pointToNextRank.ToDisplayString());
            
            // 最大ランクレベルに到達した場合、「 最大ランクレベルに到達しました」の表示に切り替える
            _achievedMaxRankObject.IsVisible = pointToNextRank.IsZero();
            _requiredPointAreaObject.IsVisible = !pointToNextRank.IsZero();
        }

        public async UniTask PlayRankPanelAnimation(
            bool isPointDecreased,
            CancellationToken cancellationToken)
        {
            await _rankPanelAnimation.PlayAsync(cancellationToken);
            _rankBackEffectObject.IsVisible = !isPointDecreased;
            _rankBackEffectAnimator.Play(RankBackEffectInAnimationName);
        }

        public async UniTask PlayPointCountAnimation(
            PvpPoint beforePoint,
            PvpPoint afterPoint,
            PvpPoint targetRankLowerRequiredPoint,
            bool isMaxRankLevel,
            CancellationToken cancellationToken)
        {
            var needPoint = targetRankLowerRequiredPoint - beforePoint;
            if (needPoint < PvpPoint.Zero)
            {
                // ランクアップに必要なスコアが0未満の場合は0にする
                needPoint = PvpPoint.Zero;
            }
            _pointText.SetText(needPoint.ToDisplayString());

            var currentAnimationPoint = beforePoint.Value;
            await DOTween.To(
                    () => currentAnimationPoint,
                    x => currentAnimationPoint = x,
                    afterPoint.Value,
                    TotalScoreCountAnimationDuration)
                .SetEase(Ease.Linear)
                .OnUpdate(() =>
                {
                    var subScore = targetRankLowerRequiredPoint.Value - currentAnimationPoint;
                    if (subScore < PvpPoint.Zero.Value)
                    {
                        // ランクアップに必要なスコアが0未満の場合は0にする
                        subScore = PvpPoint.Zero.Value;
                    }
                    _pointText.SetText(new PvpPoint(subScore).ToDisplayString());
                    // ランクMaxに到達した場合は専用表示に変える
                    bool isAchievedMaxRank = isMaxRankLevel && subScore == PvpPoint.Zero.Value;
                    _requiredPointAreaObject.IsVisible = !isAchievedMaxRank;
                    _achievedMaxRankObject.IsVisible = isAchievedMaxRank;
                })
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);

            if (isMaxRankLevel)
            {
                // 最大ランクレベルに到達した場合、「 最大ランクレベルに到達しました」の表示に切り替える
                _achievedMaxRankObject.IsVisible = true;
                _requiredPointAreaObject.IsVisible = false;
            }
        }

        public async UniTask PlayPointGaugeAnimation(
            PvpBattleResultRankAnimationGaugeRate beforeGaugeRate,
            PvpBattleResultRankAnimationGaugeRate afterGaugeRate,
            bool isAchievedRank,
            bool isMaxRankLevel,
            CancellationToken cancellationToken)
        {
            await _rankProgressGaugeImage.Image
                .DOFillAmount(afterGaugeRate.Value, TotalScoreCountAnimationDuration)
                .From(beforeGaugeRate.Value)
                .SetEase(Ease.Linear)
                .OnComplete(() =>
                {
                    if (!isAchievedRank) return;

                    if (beforeGaugeRate > afterGaugeRate)
                    {
                        _rankProgressGaugeImage.Image.fillAmount = 1f;
                    }
                    else
                    {
                        // 目標ランクが最大ランクの場合は、ゲージを1にする(満タンにする)
                        _rankProgressGaugeImage.Image.fillAmount = isMaxRankLevel ? 1f : 0f;
                    }

                })
                .WithCancellation(cancellationToken);
        }

        public async UniTask PlayRankChangeAnimation(
            PvpRankLevel nextRankLevel,
            CancellationToken cancellationToken)
        {
            await _rankIcon.PlayRankTierUpAnimation(nextRankLevel, cancellationToken);
        }

        public void PlayRankDowngradeAnimation(
            PvpRankLevel rankLevel)
        {
            _rankIcon.PlayRankTierAnimation(rankLevel);
        }

        public async UniTask PlayChangeRankIcon(
            PvpRankClassType rankType,
            CancellationToken cancellationToken)
        {
            await _rankIcon.PlayRankTypeAnimation(
                rankType,
                cancellationToken);
            _rankBackEffectAnimator.Play(RankBackEffectInAnimationName);
        }

        public void SetupRankName(
            PvpRankClassType rankType)
        {
            _rankNameText.SetText(rankType.ToDisplayString());
        }

        public void SkipRankPanelAnimation(bool isPointDecreased)
        {
            _rankPanelAnimation.Skip();
            _rankBackEffectObject.IsVisible = !isPointDecreased;
            _rankBackEffectAnimator.Play(RankBackEffectInAnimationName);
        }
    }
}
