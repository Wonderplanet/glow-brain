using GLOW.Modules.Spine.Presentation;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    [TrackBindingType(typeof(BattleEffectUsableUISpineWithOutlineAvatar))]
    [TrackClipType(typeof(IdleIncentiveUnitAnimationTrackClip))]
    public class IdleIncentiveUnitAnimationTrack : TrackAsset
    {
           public override Playable CreateTrackMixer(PlayableGraph graph, UnityEngine.GameObject gameObject, int inputCount)
            {
                var playable = ScriptPlayable<IdleIncentiveUnitAnimationTrackMixerBehaviour>.Create(graph, inputCount);
                return playable;
            }
    }
}
