using System;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [Serializable]
    public class TimelineLoopTrackBehaviour : PlayableBehaviour
    {
        public TimelineClip Clip { get; set; }
    }
}
