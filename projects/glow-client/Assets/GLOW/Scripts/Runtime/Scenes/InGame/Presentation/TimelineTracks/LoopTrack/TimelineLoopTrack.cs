using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [TrackClipType(typeof(TimelineLoopTrackClip))]
    [TrackColor(1, 0, 0)]
    public class TimelineLoopTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = ScriptPlayable<TimelineLoopTrackMixerBehaviour>.Create(graph, inputCount);
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
                var speechBalloonTrackClip = clip.asset as TimelineLoopTrackClip;
                if (speechBalloonTrackClip != null)
                {
                    speechBalloonTrackClip.Clip = clip;
                }
            }
        }
    }
}
