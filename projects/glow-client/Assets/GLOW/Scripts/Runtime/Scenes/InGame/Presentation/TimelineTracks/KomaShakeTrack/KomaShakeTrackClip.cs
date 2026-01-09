using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaShakeTrackClip : PlayableAsset, ITimelineClipAsset
    {
        public KomaShakeTrackBehaviour _behaviour = new ();

        public ClipCaps clipCaps => ClipCaps.None;

        public TimelineClip Clip { get; set; }

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<KomaShakeTrackBehaviour>.Create (graph, _behaviour);
            var behaviour = playable.GetBehaviour();

            behaviour.Clip = Clip;

            return playable;
        }
    }
}
