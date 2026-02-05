using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionDescription(ObscuredString Value)
    {
        public static MissionDescription Empty { get; } = new("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
