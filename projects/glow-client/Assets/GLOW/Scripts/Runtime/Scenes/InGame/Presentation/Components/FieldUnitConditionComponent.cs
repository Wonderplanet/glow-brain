using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Field;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class FieldUnitConditionComponent : UIObject
    {
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] Animator _animator;
        [Header("HPゲージ画像")]
        [SerializeField] UIImage _hpGaugeImage;
        [SerializeField] UIImage _defaultGauge;
        [SerializeField] UIImage _notificationUnderHalfGauge;
        [SerializeField] UIImage _notificationUnderQuarterGauge;
        [Header("状態異常")]
        [SerializeField] UnitStateEffectIcon _unitStateEffectIcon;
        
        CancellationTokenSource _unitConditionCancellationTokenSource;
        
        public FieldViewPositionTracker FieldViewPositionTracker { get; set; }
        // チュートリアル用
        public MasterDataId CharacterId { get; set; }
        
        protected override void OnDestroy()
        {
            base.OnDestroy();
            
            CancelShowAnimation();
        }

        public void SetupHpGauge(bool isVisible)
        {
            UpdateHpGauge(1f);
            ChangeVisibleImmediately(isVisible);
        }

        public void UpdateHpGauge(HP maxHp, HP currentHp)
        {
            // HPゲージの更新のための計算
            var hpRate = currentHp.PercentageTo(maxHp).ToRate();
            UpdateHpGauge(hpRate);
            PlayDamageAnimation();
        }

        public void UpdateStateEffects(
            IReadOnlyList<StateEffectType> stateEffectTypes,
            BattleStateEffectViewManager battleStateEffectViewManager)
        {
            _unitStateEffectIcon.UpdateStateEffects(stateEffectTypes, battleStateEffectViewManager);
        }
        
        public void ChangeVisibleImmediately(bool isVisible)
        {
            if(IsVisible == isVisible) return;
            CancelShowAnimation();
            
            IsVisible = isVisible;
            _canvasGroup.alpha = isVisible ? 1f : 0f;
        }

        public void ChangeVisible(bool isVisible)
        {
            if(IsVisible == isVisible) return;
            CancelShowAnimation();

            if (isVisible)
            {
                _unitConditionCancellationTokenSource = CancellationTokenSource
                    .CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy());

                Show(_unitConditionCancellationTokenSource.Token).Forget();
            }
            else
            {
                IsVisible = false;
                _canvasGroup.alpha = 0f;
            }
        }

        async UniTask Show(CancellationToken cancellationToken)
        {
            IsVisible = true;
            _canvasGroup.alpha = 0f;
            
            await _canvasGroup.DOFade(1f, 0.2f).WithCancellation(cancellationToken);
        }
        
        void CancelShowAnimation()
        {
            _unitConditionCancellationTokenSource?.Cancel();
            _unitConditionCancellationTokenSource?.Dispose();
            _unitConditionCancellationTokenSource = null;
        }
        
        void PlayDamageAnimation()
        {
            _animator.Play("Damage");
        }

        void UpdateHpGauge(float hpRate)
        {
            _hpGaugeImage.Image.fillAmount = hpRate;
            
            var isUnderHalf = hpRate <= 0.5f;
            var isUnderQuarter = hpRate <= 0.25f;
            
            // HPが半分以上の場合は通常ゲージを表示
            if (!isUnderHalf)
            {
                _defaultGauge.IsVisible = true;
                _notificationUnderHalfGauge.IsVisible = false;
                _notificationUnderQuarterGauge.IsVisible = false;
                return;
            }
            
            // HPが半分以下の場合は警告ゲージを表示(25%以下の場合は赤、それ以外はオレンジ色)
            _defaultGauge.IsVisible = false;
            _notificationUnderHalfGauge.IsVisible = !isUnderQuarter;
            _notificationUnderQuarterGauge.IsVisible = isUnderQuarter;
        }
    }
}
