using GLOW.Core.Modules.MultipleSwitchController;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class ScreenFlashTrackBehaviour : PlayableBehaviour
    {
        public TimelineClip Clip { get; set; }
        public MultipleSwitchHandler FlashHandler { get; set; }

        public bool IsFlashing { get; set; }

        public override void OnPlayableDestroy(Playable playable)
        {
            base.OnPlayableDestroy(playable);

            FlashHandler?.Dispose();
            FlashHandler = null;

            IsFlashing = false;
        }
    }
}
