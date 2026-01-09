using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventDisplayUnitSpeechBalloonText(ObscuredString Value)
    {
        public static EventDisplayUnitSpeechBalloonText Empty { get; } = new EventDisplayUnitSpeechBalloonText(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
