using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class KomaZoomTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中以外は無視
            if (!Application.isPlaying) return;

            var komaZoomTrackClipDelegate = playerData as IKomaZoomTrackClipDelegate;
            if (komaZoomTrackClipDelegate == null) return;

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    if (!behaviour.IsZooming)
                    {
                        behaviour.KomaId = komaZoomTrackClipDelegate.GetKomaId(behaviour.AutoPlayerSequenceElementIdOfTargetUnit);
                        behaviour.IsZooming = true;
                    }

                    var normalizedTime = (float)((time - clip.start) / clip.duration);
                    var zoomRate = Mathf.Lerp(behaviour.StartZoomRate, behaviour.EndZoomRate, normalizedTime);

                    komaZoomTrackClipDelegate.SetKomaZoomRate(
                        behaviour.KomaId,
                        behaviour.AutoPlayerSequenceElementIdOfTargetUnit,
                        zoomRate);
                }
            }
        }

        List<KomaZoomTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<KomaZoomTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<KomaZoomTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
