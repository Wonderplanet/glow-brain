using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    [TrackBindingType(typeof(CanvasGroup))]
    [TrackClipType(typeof(IdleIncentiveTopCanvasGroupFadeTrackClip))]
    public class IdleIncentiveTopCanvasGroupFadeTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, GameObject go, int inputCount)
        {
            var playable = ScriptPlayable<IdleIncentiveTopCanvasGroupFadeTrackMixerBehaviour>.Create(graph, inputCount);
            return playable;
        }
    }
}
