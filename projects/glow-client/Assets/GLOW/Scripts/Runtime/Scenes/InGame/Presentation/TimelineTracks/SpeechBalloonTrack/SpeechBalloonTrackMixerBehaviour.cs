using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public class SpeechBalloonTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }
        public AutoPlayerSequenceElementId AutoPlayerSequenceElementId { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            // 実行中以外は無視
            if (!Application.isPlaying) return;

            var speechBalloonTrackClipDelegate = playerData as ISpeechBalloonTrackClipDelegate;
            if (speechBalloonTrackClipDelegate == null) return;

            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    var timeOffset = new SpeechBalloonAnimationTime((float)(time - clip.start));

                    if (!behaviour.IsSpeaking)
                    {
                        var speechBalloon = speechBalloonTrackClipDelegate.GenerateSpeechBalloon(
                            AutoPlayerSequenceElementId,
                            behaviour.SpeechBalloonText,
                            timeOffset);

                        behaviour.IsSpeaking = true;
                        behaviour.SpeechBalloon = speechBalloon;
                    }

                    if (behaviour.SpeechBalloon != null)
                    {
                        behaviour.SpeechBalloon.SetAnimationTime(timeOffset);
                    }
                }

                if (behaviour.IsSpeaking && (time >= clip.end || time >= Director.duration))
                {
                    if (behaviour.SpeechBalloon != null)
                    {
                        behaviour.SpeechBalloon.EndSpeech();
                    }

                    behaviour.IsSpeaking = false;
                    behaviour.SpeechBalloon = null;
                }
            }
        }

        List<SpeechBalloonTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<SpeechBalloonTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<SpeechBalloonTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
