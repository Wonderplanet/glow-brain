using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    [Serializable]
    public class SpeechBalloonTrackBehaviour : PlayableBehaviour
    {
        [SerializeField] SpeechBalloonType _speechBalloonType;
        [SerializeField] SpeechBalloonSide _speechBalloonSide;
        [SerializeField] string _text;

        public TimelineClip Clip { get; set; }
        public string Text => _text;
        public SpeechBalloonText SpeechBalloonText => new (_speechBalloonType, _speechBalloonSide, SpeechBalloonAnimationTime.Empty, _text);
        public bool IsSpeaking { get; set; }
        public ISpeechBalloonTrackSpeechBalloon SpeechBalloon { get; set; }

        public override void OnPlayableDestroy(Playable playable)
        {
            base.OnPlayableDestroy(playable);

            if (SpeechBalloon != null)
            {
                SpeechBalloon.EndSpeech();
                SpeechBalloon = null;
            }

            IsSpeaking = false;
        }
    }
}
