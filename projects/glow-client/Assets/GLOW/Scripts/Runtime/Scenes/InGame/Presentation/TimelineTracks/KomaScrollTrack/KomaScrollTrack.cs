using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [TrackBindingType(typeof(MangaAnimation))]
    [TrackClipType(typeof(KomaScrollTrackClip))]
    public class KomaScrollTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = ScriptPlayable<KomaScrollTrackMixerBehaviour>.Create(graph, inputCount);
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
                var komaScrollTrackClip = clip.asset as KomaScrollTrackClip;
                if (komaScrollTrackClip != null)
                {
                    komaScrollTrackClip.Clip = clip;
                }
            }
        }
    }
}
