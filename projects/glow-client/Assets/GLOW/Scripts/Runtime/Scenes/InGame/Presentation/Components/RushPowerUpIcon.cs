using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class RushPowerUpIcon : MonoBehaviour
    {
        static readonly int PowerUpIconAnimationTriggerShowPowerUpIcon = Animator.StringToHash("ShowPowerUpIcon");
        static readonly int PowerUpIconAnimationTriggerOnRushAttackPowerUp = Animator.StringToHash("OnRushAttackPowerUp");

        [SerializeField] UIObject _powerUpIconEffectRoot;
        [SerializeField] UIObject _powerUpIconLightEffect;
        [SerializeField] UIText _powerUpPercentageText;
        [SerializeField] Animator _powerUpIconAnimator;

        public void UpdateIcon(RushPowerUpStateEffectBonus rushPowerUpStateEffectBonus)
        {
            // 演出の都合上、威力上昇アイコン表示はOnRushAttackPowerUpのみで行うようにし、
            // 非表示に関しては増加演出中でも差し込みで非表示にできるようにする
            var isZero = rushPowerUpStateEffectBonus.IsZero();
            _powerUpIconAnimator.SetBool(PowerUpIconAnimationTriggerShowPowerUpIcon, !isZero);
        }

        public void OnRushAttackPowerUp(Vector3 iconPos, PercentageM updatedPowerUp)
        {
            _powerUpPercentageText.SetText(updatedPowerUp.ToString());
            _powerUpIconAnimator.SetTrigger(PowerUpIconAnimationTriggerOnRushAttackPowerUp);

            // 威力上昇効果発動ユニットの上部に表示していたアイコンの消えた位置(iconPos)から
            // 威力上昇アイコンまでの座標差異をもとに向きを求め、光エフェクトに角度を設定する
            var dir = iconPos - _powerUpIconEffectRoot.RectTransform.position;
            dir.z = 0f;
            var setRotation = Quaternion.FromToRotation(Vector3.up, dir.normalized);
            _powerUpIconLightEffect.transform.rotation = setRotation;

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_073);
        }
    }
}
