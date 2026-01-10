using System;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Core.Presentation.Modules.Audio
{
    [Serializable]
    public class SoundEffectTrackBehaviour : PlayableBehaviour
    {
        public TimelineClip Clip { get; set; }
        public SoundEffectId SoundEffectId { get; set; }
        public bool IsPlayed { get; set; }
        public bool IsPlaying { get; set; }
        
        public override void OnGraphStop(Playable playable)
        {
            base.OnGraphStop(playable);
            
            if (IsPlaying)
            {
                SoundEffectPlayer.Stop(SoundEffectId);
                IsPlaying = false;
            }
        }

        public override void OnPlayableDestroy(Playable playable)
        {
            base.OnPlayableDestroy(playable);
            
            if (IsPlaying)
            {
                SoundEffectPlayer.Stop(SoundEffectId);
                IsPlaying = false;
            }
        }
    }
}