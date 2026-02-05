using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitSpeechBalloonModel(FieldObjectId SpeakerId, SpeechBalloonModel SpeechBalloon)
    {
        public static UnitSpeechBalloonModel Empty { get; } = new(FieldObjectId.Empty, SpeechBalloonModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
