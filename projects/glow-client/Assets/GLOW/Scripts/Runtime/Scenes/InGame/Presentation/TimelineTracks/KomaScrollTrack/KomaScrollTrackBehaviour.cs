using System;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [Serializable]
    public class KomaScrollTrackBehaviour : PlayableBehaviour
    {
        [SerializeField] string _autoPlayerSequenceElementIdOfTargetUnit;
        [SerializeField] AnimationCurve _animationCurve = AnimationCurve.EaseInOut(0, 0, 1, 1);

        public TimelineClip Clip { get; set; }
        public AutoPlayerSequenceElementId AutoPlayerSequenceElementIdOfTargetUnit => new (_autoPlayerSequenceElementIdOfTargetUnit);
        public AnimationCurve AnimationCurve => _animationCurve;
        public float StartScrollPosition { get; set; }
        public float TargetScrollPosition { get; set; }
        public bool IsScrolling { get; set; }
    }
}
