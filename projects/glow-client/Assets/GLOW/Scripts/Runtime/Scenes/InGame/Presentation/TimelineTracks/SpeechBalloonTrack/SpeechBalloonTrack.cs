using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [TrackBindingType(typeof(MangaAnimation))]
    [TrackClipType(typeof(SpeechBalloonTrackClip))]
    public class SpeechBalloonTrack : TrackAsset
    {
        [SerializeField] string _autoPlayerSequenceElementId;

        public override Playable CreateTrackMixer(PlayableGraph graph, GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = ScriptPlayable<SpeechBalloonTrackMixerBehaviour>.Create(graph, inputCount);
            var director = gameObj.GetComponent<PlayableDirector>();
            var mixer = playable.GetBehaviour();

            mixer.Director = director;
            mixer.AutoPlayerSequenceElementId = new AutoPlayerSequenceElementId(_autoPlayerSequenceElementId);

            return playable;
        }

        void SetupClipReference()
        {
            var clips = GetClips();
            foreach (var clip in clips)
            {
                var speechBalloonTrackClip = clip.asset as SpeechBalloonTrackClip;
                if (speechBalloonTrackClip != null)
                {
                    speechBalloonTrackClip.Clip = clip;
                    clip.displayName = speechBalloonTrackClip.SpeechText;
                }
            }
        }
    }
}
