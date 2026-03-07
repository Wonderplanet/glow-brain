using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class ScreenFlashTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中以外は無視
            if (!Application.isPlaying) return;

            var screenFlashTrackClipDelegate = playerData as IScreenFlashTrackClipDelegate;
            if (screenFlashTrackClipDelegate == null) return;

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    if (!behaviour.IsFlashing)
                    {
                        behaviour.FlashHandler = screenFlashTrackClipDelegate.StartFlash();
                        behaviour.IsFlashing = true;
                    }
                }

                if (behaviour.IsFlashing && (time >= clip.end || time >= Director.duration))
                {
                    behaviour.FlashHandler?.Dispose();
                    behaviour.IsFlashing = false;
                }
            }
        }

        List<ScreenFlashTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<ScreenFlashTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<ScreenFlashTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
