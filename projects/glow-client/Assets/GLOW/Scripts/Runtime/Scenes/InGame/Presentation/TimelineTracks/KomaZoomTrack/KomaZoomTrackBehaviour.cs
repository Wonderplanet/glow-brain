using System;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [Serializable]
    public class KomaZoomTrackBehaviour : PlayableBehaviour
    {
        [SerializeField] string _autoPlayerSequenceElementIdOfTargetUnit;
        [SerializeField] float _startZoomRate;
        [SerializeField] float _endZoomRate;

        public TimelineClip Clip { get; set; }
        public AutoPlayerSequenceElementId AutoPlayerSequenceElementIdOfTargetUnit => new (_autoPlayerSequenceElementIdOfTargetUnit);
        public float StartZoomRate => _startZoomRate;
        public float EndZoomRate => _endZoomRate;

        public KomaId KomaId { get; set; }
        public bool IsZooming { get; set; }
    }
}
