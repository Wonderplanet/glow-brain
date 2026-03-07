using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class RushChargeCoolTimeBonusIcon : MonoBehaviour
    {
        static readonly int IconAnimationShowTrigger = Animator.StringToHash("ShowPowerUpIcon");
        static readonly int IconAnimationOnTrigger = Animator.StringToHash("OnRushAttackPowerUp");

        [SerializeField] UIText _bonusPercentageText;
        [SerializeField] Animator _bonusIconAnimator;

        public void Initialize(TickCount rushChargeCoolTimeBonus)
        {
            var isZero = rushChargeCoolTimeBonus.IsZero();
            _bonusIconAnimator.SetBool(IconAnimationShowTrigger, !isZero);

            if (!isZero)
            {
                _bonusPercentageText.SetText(rushChargeCoolTimeBonus.ToSecondsString());
                _bonusIconAnimator.SetTrigger(IconAnimationOnTrigger);
            }
        }
    }
}
