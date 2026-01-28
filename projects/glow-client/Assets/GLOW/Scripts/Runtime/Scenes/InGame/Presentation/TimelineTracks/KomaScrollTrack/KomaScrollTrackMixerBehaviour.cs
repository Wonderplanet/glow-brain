using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaScrollTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中以外は無視
            if (!Application.isPlaying) return;

            var komaScrollTrackClipDelegate = playerData as IKomaScrollTrackClipDelegate;
            if (komaScrollTrackClipDelegate == null) return;

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    if (!behaviour.IsScrolling)
                    {
                        behaviour.StartScrollPosition = komaScrollTrackClipDelegate.GetCurrentKomaScrollPosition();

                        behaviour.TargetScrollPosition = komaScrollTrackClipDelegate
                            .GetKomaScrollPosition(behaviour.AutoPlayerSequenceElementIdOfTargetUnit);

                        behaviour.IsScrolling = true;
                    }

                    var normalizedTime = (float)((time - clip.start) / clip.duration);
                    var animationValue = behaviour.AnimationCurve.Evaluate(normalizedTime);
                    var scrollLength = behaviour.TargetScrollPosition - behaviour.StartScrollPosition;
                    var scrollPosition = behaviour.StartScrollPosition + scrollLength * animationValue;

                    komaScrollTrackClipDelegate.SetKomaScrollPosition(scrollPosition);
                }
            }
        }

        List<KomaScrollTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<KomaScrollTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<KomaScrollTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
