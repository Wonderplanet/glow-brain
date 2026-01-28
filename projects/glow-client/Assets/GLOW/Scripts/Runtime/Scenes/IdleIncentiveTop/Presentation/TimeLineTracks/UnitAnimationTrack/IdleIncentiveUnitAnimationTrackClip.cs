using GLOW.Scenes.InGame.Presentation.Constants;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveUnitAnimationTrackClip : PlayableAsset, ITimelineClipAsset
    {
        [Header("使用可能なアニメーション\nWait, Move, Attack, SpecialAttackCutIn, Death, Appearing")]
       [SerializeField] UnitAnimationType _unitAnimationType;

        ClipCaps _clipCaps;

        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<IdleIncentiveUnitAnimationTrackBehaviour>.Create(graph);
            playable.GetBehaviour().UnitAnimationType = _unitAnimationType;
            return playable;
        }

        ClipCaps ITimelineClipAsset.clipCaps => _clipCaps;
    }
}
