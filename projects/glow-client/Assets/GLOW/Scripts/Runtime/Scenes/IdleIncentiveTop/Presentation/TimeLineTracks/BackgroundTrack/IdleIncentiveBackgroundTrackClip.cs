using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveBackgroundTrackClip : PlayableAsset, ITimelineClipAsset
    {
        [SerializeField] IdleIncentiveBackgroundTrackBehaviour _behaviour = new ();
        [SerializeField] int _scrollRange;
        [SerializeField] AnimationCurve _scrollCurve;

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<IdleIncentiveBackgroundTrackBehaviour>.Create(graph, _behaviour);
            var behaviour = playable.GetBehaviour();

            behaviour.ScrollRange = _scrollRange;
            behaviour.ScrollCurve = _scrollCurve;

            return playable;
        }

        ClipCaps ITimelineClipAsset.clipCaps => ClipCaps.None;
    }
}
