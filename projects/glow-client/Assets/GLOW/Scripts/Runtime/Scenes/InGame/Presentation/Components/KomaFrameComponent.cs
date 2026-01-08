using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaFrameComponent : UIObject
    {
        [SerializeField] UIObject _glow;
        [SerializeField] UIObject _glowSpecialAttackCoordinateRange;
        [SerializeField] CanvasGroup _glowCanvasGroup;
        [SerializeField] CanvasGroup _glowSpecialAttackCoordinateRangeCanvasGroup;

        public void StartGlow()
        {
            _glow.Hidden = false;
            _glowCanvasGroup.alpha = 1f;
        }

        public void StartSpecialAttackCoordinateRangeGlow()
        {
            _glowSpecialAttackCoordinateRange.Hidden = false;
            _glowSpecialAttackCoordinateRangeCanvasGroup.alpha = 1f;
        }

        public void StopGlow()
        {
            _glow.Hidden = true;
        }

        public void StopSpecialAttackCoordinateRangeGlow()
        {
            _glowSpecialAttackCoordinateRange.Hidden = true;
        }

        public void SetGlowVisible(bool isVisible)
        {
            _glowCanvasGroup.alpha = isVisible ? 1f : 0f;
        }

        public void SetSpecialAttackCoordinateRangeGlowVisible(bool isVisible)
        {
            _glowSpecialAttackCoordinateRangeCanvasGroup.alpha = isVisible ? 1f : 0f;
        }
    }
}
