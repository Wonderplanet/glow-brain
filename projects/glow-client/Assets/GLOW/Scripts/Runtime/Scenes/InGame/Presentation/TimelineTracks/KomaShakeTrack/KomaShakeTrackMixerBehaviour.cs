using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaShakeTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中以外は無視
            if (!Application.isPlaying) return;

            var komaShakeTrackClipDelegate = playerData as IKomaShakeTrackClipDelegate;
            if (komaShakeTrackClipDelegate == null) return;

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    if (!behaviour.IsShaking)
                    {
                        behaviour.ShakeHandler = komaShakeTrackClipDelegate.StartShake();
                        behaviour.IsShaking = true;
                    }
                }

                if (behaviour.IsShaking && (time >= clip.end || time >= Director.duration))
                {
                    behaviour.ShakeHandler?.Dispose();
                    behaviour.IsShaking = false;
                }
            }
        }

        List<KomaShakeTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<KomaShakeTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<KomaShakeTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
