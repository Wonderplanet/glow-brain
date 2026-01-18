using UnityEngine;

namespace GLOW.Core.Presentation.Modules.Audio
{
    public class FunctionSoundEffectPlayer : MonoBehaviour
    {
        public void PlaySE(SoundEffectId seId)
        {
            SoundEffectPlayer.Play(seId);
        }

        public void StopSE(SoundEffectId seId)
        {
            SoundEffectPlayer.Stop(seId);
        }
    }
}