using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameUnitDetailBalloonMessage(ObscuredString Value)
    {
        public static InGameUnitDetailBalloonMessage Empty { get; } = new(string.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
