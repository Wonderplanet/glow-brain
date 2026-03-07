using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceLevelUpStatusAnimationComponent : MonoBehaviour
    {
        [SerializeField] Animator _hpLevelUpAnimation;
        [SerializeField] UIText _hpLevelUpText;
        [SerializeField] Animator _attackPowerLevelUpAnimation;
        [SerializeField] UIText _attackPowerLevelUpText;
        [SerializeField] Animator _rushLevelUpAnimation;
        [SerializeField] UIText _rushLevelUpText;

        readonly string _animationTrigger = "Play";

        void Awake()
        {
            _hpLevelUpAnimation.gameObject.SetActive(false);
            _attackPowerLevelUpAnimation.gameObject.SetActive(false);
            _rushLevelUpAnimation.gameObject.SetActive(false);
        }

        public void PlayAnimation(HP addHp, AttackPower addAttackPower)
        {
            if (addHp.Value > 0)
            {
                _hpLevelUpAnimation.gameObject.SetActive(true);
                _hpLevelUpAnimation.SetTrigger(_animationTrigger);
                _hpLevelUpText.SetText("+" + addHp.Value);
            }

            if (addAttackPower.Value > 0)
            {
                _attackPowerLevelUpAnimation.gameObject.SetActive(true);
                _attackPowerLevelUpAnimation.SetTrigger(_animationTrigger);
                _attackPowerLevelUpText.SetText("+" + addAttackPower.Value);
            }
        }

        public void PlaySpecialUnitAnimation(AttackPower addRush)
        {
            if (addRush.Value > 0)
            {
                _rushLevelUpAnimation.gameObject.SetActive(true);
                _rushLevelUpAnimation.SetTrigger(_animationTrigger);
                _rushLevelUpText.SetText("+{0}%", addRush.ToRushPercentageM().ToStringF2());
            }
        }

        public void EndAnimation()
        {
            _hpLevelUpAnimation.gameObject.SetActive(false);
            _attackPowerLevelUpAnimation.gameObject.SetActive(false);
            _rushLevelUpAnimation.gameObject.SetActive(false);
        }
    }
}
