using GLOW.Core.Modules.MultipleSwitchController;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaShakeTrackBehaviour : PlayableBehaviour
    {
        public TimelineClip Clip { get; set; }
        public MultipleSwitchHandler ShakeHandler { get; set; }

        public bool IsShaking { get; set; }

        public override void OnPlayableDestroy(Playable playable)
        {
            base.OnPlayableDestroy(playable);

            ShakeHandler?.Dispose();
            ShakeHandler = null;

            IsShaking = false;
        }
    }
}
