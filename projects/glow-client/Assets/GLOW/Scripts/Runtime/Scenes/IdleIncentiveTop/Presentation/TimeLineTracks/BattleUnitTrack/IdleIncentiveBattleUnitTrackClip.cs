using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks.CombatUnitTrack
{
    public class IdleIncentiveBattleUnitTrackClip : PlayableAsset, ITimelineClipAsset
    {
        public TimelineClip Clip { get; set; }

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<IdleIncentiveBattleUnitTrackBehaviour>.Create(graph);
            var behaviour = playable.GetBehaviour();
            behaviour.Start = Clip.start;
            behaviour.End = Clip.end;

            return playable;
        }

        ClipCaps ITimelineClipAsset.clipCaps => ClipCaps.None;
    }
}
