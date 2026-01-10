using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [TrackBindingType(typeof(MangaAnimation))]
    [TrackClipType(typeof(KomaZoomTrackClip))]
    public class KomaZoomTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, UnityEngine.GameObject gameObj, int inputCount)
        {
            SetupClipReference();

            var playable = UnityEngine.Playables.ScriptPlayable<KomaZoomTrackMixerBehaviour>.Create(graph, inputCount);
            var director = gameObj.GetComponent<UnityEngine.Playables.PlayableDirector>();
            var mixer = playable.GetBehaviour();

            mixer.Director = director;

            return playable;
        }

        void SetupClipReference()
        {
            var clips = GetClips();
            foreach (var clip in clips)
            {
                var komaZoomTrackClip = clip.asset as KomaZoomTrackClip;
                if (komaZoomTrackClip != null)
                {
                    komaZoomTrackClip.Clip = clip;
                }
            }
        }
    }
}
