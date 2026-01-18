using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.Mission;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionProgressGaugeComponent : UIBehaviour
    {
        [SerializeField] Slider _progressGaugeSlider;
        
        public void SetProgressGaugeRate(float rate)
        {
            _progressGaugeSlider.value = rate;
        }

        public async UniTask ProgressGaugeAnimation(
            CancellationToken cancellationToken,
            BonusPoint updatedBonusPoint, 
            BonusPoint maxBonusPoint)
        {
            var rate = updatedBonusPoint.ToGaugeRate(maxBonusPoint);
            await PlayProgressGaugeAnimation(
                cancellationToken, 
                rate,
                0.4f);
        }
        
        public async UniTask ProgressGaugeAnimation(
            CancellationToken cancellationToken,
            LoginDayCount beforeTotalLoginDayCount,
            LoginDayCount updatedTotalLoginDayCount,
            LoginDayCount start, 
            LoginDayCount end, 
            LoginDayCount interval)
        {
            var currentRate = beforeTotalLoginDayCount.ToGaugeRate(start, end, interval);
            SetProgressGaugeRate(currentRate);
            
            var rate = updatedTotalLoginDayCount.ToGaugeRate(start, end, interval);
            await PlayProgressGaugeAnimation(
                cancellationToken, 
                rate,
                0.4f);
        }
        
        public async UniTask PlayProgressGaugeAnimation(
            CancellationToken cancellationToken, 
            float rate, 
            float duration)
        {
            var currentRate = _progressGaugeSlider.value;
            await DOTween.To(
                    () => currentRate, 
                    (x) => _progressGaugeSlider.value = x,
                    rate,
                    duration)
                .SetEase(Ease.OutQuart)
                .WithCancellation(cancellationToken);
        }
    }
}