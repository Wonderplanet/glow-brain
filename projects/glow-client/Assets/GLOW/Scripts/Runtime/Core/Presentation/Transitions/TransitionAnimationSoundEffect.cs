using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;

namespace GLOW.Core.Presentation.Transitions
{
    public class TransitionAnimationSoundEffect : MonoBehaviour
    {
        public void OnTransitionSoundEffectSwipe()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_068);
        }

        public void OnTransitionSoundEffectShowPlusMark()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_001);
        }

        public void OnTransitionSoundEffectHidePlusMark()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_002);
        }
    }
}
