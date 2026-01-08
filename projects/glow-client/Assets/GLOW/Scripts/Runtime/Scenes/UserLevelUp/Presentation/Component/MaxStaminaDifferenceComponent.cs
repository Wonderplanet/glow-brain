using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UserLevelUp.Presentation.Component
{
    public class MaxStaminaDifferenceComponent : UIObject
    {
        [SerializeField] UIText _beforeMaxStaminaText;
        [SerializeField] UIText _afterMaxStaminaText;
        [SerializeField] CanvasGroup _canvasGroup;
        
        public void SetupMaxStamina(Stamina beforeMaxStamina, Stamina afterMaxStamina)
        {
            _beforeMaxStaminaText.SetText(beforeMaxStamina.ToString());
            _afterMaxStaminaText.SetText(afterMaxStamina.ToString());
            
            _canvasGroup.alpha = 0.0f;
        }
        
        public async UniTask PlayFadeIn(CancellationToken cancellationToken)
        {
            Hidden = false;
            _canvasGroup.alpha = 0.0f;
            await _canvasGroup.DOFade(1.0f, 0.35f)
                .WithCancellation(cancellationToken);;
        }
        
        public void SkipFadeIn()
        {
            Hidden = false;
            _canvasGroup.alpha = 1.0f;
        }
    }
}