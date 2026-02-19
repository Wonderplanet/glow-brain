using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Core.Presentation.Modules.Audio
{
    [TrackClipType(typeof(SoundEffectTrackClip))]
    public class SoundEffectTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = ScriptPlayable<SoundEffectTrackMixerBehaviour>.Create(graph, inputCount);
            var director = gameObj.GetComponent<PlayableDirector>();
            var mixer = playable.GetBehaviour();

            mixer.Director = director;

            return playable;
        }

        void SetupClipReference()
        {
            var clips = GetClips();
            foreach (var clip in clips)
            {
                var soundEffectTrackClip = clip.asset as SoundEffectTrackClip;
                if (soundEffectTrackClip == null) continue;
                
                soundEffectTrackClip.Clip = clip;
                clip.displayName = soundEffectTrackClip.SoundEffectId.ToString();
            }
        }
    }
}