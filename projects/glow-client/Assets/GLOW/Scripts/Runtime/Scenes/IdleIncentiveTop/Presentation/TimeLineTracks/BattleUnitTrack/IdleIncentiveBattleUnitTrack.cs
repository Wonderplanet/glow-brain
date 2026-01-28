using GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks.CombatUnitTrack;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    [TrackClipType(typeof(IdleIncentiveBattleUnitTrackClip))]
    [TrackBindingType(typeof(IdleIncentiveTimeLineAnimationControl))]
    public class IdleIncentiveBattleUnitTrack : TrackAsset
    {
        public override Playable CreateTrackMixer(PlayableGraph graph, UnityEngine.GameObject gameObject, int inputCount)
        {
            SetupClipReference();
            var playable = ScriptPlayable<IdleIncentiveBattleUnitTrackMixerBehaviour>.Create(graph, inputCount);
            var director = gameObject.GetComponent<PlayableDirector>();
            var mixer = playable.GetBehaviour();

            mixer.Director = director;

            return playable;
        }

        void SetupClipReference()
        {
            var clips = GetClips();
            foreach (var clip in clips)
            {
                var battleUnitTrackClip = clip.asset as IdleIncentiveBattleUnitTrackClip;
                if (battleUnitTrackClip != null)
                {
                    battleUnitTrackClip.Clip = clip;
                }
            }
        }    }
}
