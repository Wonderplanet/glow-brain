using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class SpeechBalloonTrackClip : PlayableAsset, ITimelineClipAsset
    {
        public SpeechBalloonTrackBehaviour _behaviour = new ();

        public ClipCaps clipCaps => ClipCaps.None;

        public TimelineClip Clip { get; set; }
        public string SpeechText => _behaviour.Text;

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<SpeechBalloonTrackBehaviour>.Create (graph, _behaviour);
            var behaviour = playable.GetBehaviour();

            behaviour.Clip = Clip;

            return playable;
        }
    }
}
