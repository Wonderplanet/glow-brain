using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeHeaderExpGauge : UIObject
    {
        [SerializeField] UIImage _expGaugeImage;
        [SerializeField] HomeHeaderLevelUpEffect _levelUpEffect;
        
        public void SetExpGauge(RelativeUserExp current, RelativeUserExp max)
        {
            _expGaugeImage.Image.fillAmount = !max.IsZero() ? (float)current.Value / max.Value : 1f;
        }
        
        public async UniTask PlayGaugeAnimation(
            CancellationToken cancellationToken,
            float duration,
            UserExpGainViewModel userExpGainViewModel)
        {
            // 経験値ゲージを増加させる演出
            var startExpRatio = !userExpGainViewModel.NextLevelExp.IsZero()
                ? userExpGainViewModel.StartExp / userExpGainViewModel.NextLevelExp
                : 1f;

            var endExpRatio = !userExpGainViewModel.NextLevelExp.IsZero()
                ? userExpGainViewModel.EndExp / userExpGainViewModel.NextLevelExp
                : 1f;

            _expGaugeImage.Image.fillAmount = startExpRatio;

            await DOTween.To(
                    () => _expGaugeImage.Image.fillAmount,
                    value => _expGaugeImage.Image.fillAmount = value,
                    endExpRatio,
                    duration)
                .WithCancellation(cancellationToken);
        }
        
        public async UniTask PlayLevelUpEffectAsync(CancellationToken cancellationToken, bool isLevelMax)
        {
            _expGaugeImage.Image.fillAmount = isLevelMax ? 1f : 0f;
            await _levelUpEffect.PlayLevelUpEffectAsync(cancellationToken);
        }
    }
}