using UnityEngine.Playables;
using UnityEngine.Timeline;
using UnityEngine.UI;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    [TrackBindingType(typeof(Image))]
    [TrackClipType(typeof(IdleIncentiveBackgroundTrackClip))]
    public class IdleIncentiveBackgroundTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, UnityEngine.GameObject gameObject, int inputCount)
        {
            var playable = ScriptPlayable<IdleIncentiveBackgroundTrackMixerBehaviour>.Create(graph, inputCount);
            return playable;
        }
    }
}
