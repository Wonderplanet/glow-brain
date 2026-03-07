using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpeechBalloonModel(SpeechBalloonConditionType ConditionType, SpeechBalloonText SpeechBalloonText)
    {
        public static SpeechBalloonModel Empty { get; } = new(SpeechBalloonConditionType.Summon, SpeechBalloonText.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
