using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveBattleUnitTrackBehaviour : PlayableBehaviour
    {
        public double Start { get; set; }
        public double End { get; set; }
        public bool IsEnterClip { get; set; }
    }
}
