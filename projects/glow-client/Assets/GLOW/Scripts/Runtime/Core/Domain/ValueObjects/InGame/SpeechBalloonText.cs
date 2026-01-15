using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record SpeechBalloonText(
        SpeechBalloonType BalloonType,
        SpeechBalloonSide Side,
        SpeechBalloonAnimationTime Duration,
        ObscuredString Text)
    {
        public static SpeechBalloonText Empty { get; } = new(
            SpeechBalloonType.Maru,
            SpeechBalloonSide.Right,
            SpeechBalloonAnimationTime.Empty,
            string.Empty);

        public int TextLength => Text.ToString().Length;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
