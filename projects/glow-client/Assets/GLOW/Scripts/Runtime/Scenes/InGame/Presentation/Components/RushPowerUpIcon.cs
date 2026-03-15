using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class RushPowerUpIcon : MonoBehaviour
    {
        static readonly int PowerUpIconAnimationShowTrigger = Animator.StringToHash("ShowPowerUpIcon");
        static readonly int PowerUpIconAnimationOnTrigger = Animator.StringToHash("OnRushAttackPowerUp");

        [SerializeField] UIObject _powerUpIconEffectRoot;
        [SerializeField] UIObject _powerUpIconLightEffect;
        [SerializeField] UIText _powerUpPercentageText;
        [SerializeField] Animator _powerUpIconAnimator;

        bool _isArtworkEffectPowerUpBonusActive;

        public void Initialize(PercentageM artworkEffectPowerUpBonus)
        {
            // 原画効果のボーナスは100の場合、×1.0になりボーナスとしては存在しないため、IsHundredでボーナスが存在するかどうか判断する
            var isZero = artworkEffectPowerUpBonus.IsHundred();
            _isArtworkEffectPowerUpBonusActive = !isZero;
            _powerUpIconAnimator.SetBool(PowerUpIconAnimationShowTrigger, !isZero);
            if (isZero) return;

            var displayPowerUpBonus = artworkEffectPowerUpBonus - PercentageM.Hundred;
            _powerUpPercentageText.SetText(displayPowerUpBonus.ToString());
            _powerUpIconAnimator.SetTrigger(PowerUpIconAnimationOnTrigger);
        }

        public void UpdateIcon(RushPowerUpStateEffectBonus rushPowerUpStateEffectBonus)
        {
            // 演出の都合上、威力上昇アイコン表示はOnRushAttackPowerUpのみで行うようにし、
            // 非表示に関しては増加演出中でも差し込みで非表示にできるようにする
            var isZero = rushPowerUpStateEffectBonus.IsZero();
            _powerUpIconAnimator.SetBool(PowerUpIconAnimationShowTrigger,
                !isZero || _isArtworkEffectPowerUpBonusActive);
        }

        public void OnRushAttackPowerUp(Vector3 iconPos, PercentageM updatedPowerUp)
        {
            _powerUpPercentageText.SetText(updatedPowerUp.ToString());
            _powerUpIconAnimator.SetTrigger(PowerUpIconAnimationOnTrigger);

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
