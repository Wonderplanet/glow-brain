using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaScrollTrackClip : PlayableAsset, ITimelineClipAsset
    {
        public KomaScrollTrackBehaviour _behaviour = new ();

        public ClipCaps clipCaps => ClipCaps.None;

        public TimelineClip Clip { get; set; }

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<KomaScrollTrackBehaviour>.Create (graph, _behaviour);
            var behaviour = playable.GetBehaviour();

            behaviour.Clip = Clip;

            return playable;
        }
    }
}
