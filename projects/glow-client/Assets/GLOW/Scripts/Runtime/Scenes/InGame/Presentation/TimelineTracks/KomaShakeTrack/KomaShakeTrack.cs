using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [TrackBindingType(typeof(Object))]
    [TrackClipType(typeof(KomaShakeTrackClip))]
    public class KomaShakeTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, UnityEngine.GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = UnityEngine.Playables.ScriptPlayable<KomaShakeTrackMixerBehaviour>.Create(graph, inputCount);
            var director = gameObj.GetComponent<UnityEngine.Playables.PlayableDirector>();
            var mixer = playable.GetBehaviour();

            mixer.Director = director;

            return playable;
        }

        void SetupClipReference()
        {
            var clips = GetClips();
            foreach (var clip in clips)
            {
                var komaShakeTrackClip = clip.asset as KomaShakeTrackClip;
                if (komaShakeTrackClip != null)
                {
                    komaShakeTrackClip.Clip = clip;
                }
            }
        }
    }
}
