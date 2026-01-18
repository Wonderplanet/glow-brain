using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record SpeechBalloonAnimationTime(ObscuredFloat Value)
    {
        public static SpeechBalloonAnimationTime Empty { get; } = new(0);
        public static SpeechBalloonAnimationTime Zero { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
