using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveTopCanvasGroupFadeTrackClip : PlayableAsset, ITimelineClipAsset
    {
        [SerializeField] AnimationCurve _animationCurve;
        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<IdleIncentiveTopCanvasGroupFadeTrackBehaviour>.Create(graph);
            var behaviour = playable.GetBehaviour();
            behaviour.AnimationCurve = _animationCurve;
            return playable;
        }

        ClipCaps ITimelineClipAsset.clipCaps => ClipCaps.None;
    }
}
