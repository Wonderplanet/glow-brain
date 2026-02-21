using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveBackgroundTrackBehaviour : PlayableBehaviour
    {
        public int ScrollRange { get; set; }
        public AnimationCurve ScrollCurve { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            var time = playable.GetTime();
            var image = playerData as UnityEngine.UI.Image;
            if (image == null) return;

            base.ProcessFrame(playable, info, playerData);

            var duration = playable.GetDuration();
            var offset = image.material.mainTextureOffset;
            offset.x = ScrollCurve.Evaluate((float)(time / duration)) * ScrollRange;

            image.material.mainTextureOffset = offset;
        }
    }
}
