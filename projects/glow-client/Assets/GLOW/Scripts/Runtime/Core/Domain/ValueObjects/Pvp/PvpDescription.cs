using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpDescription(ObscuredString Value)
    {
        public static PvpDescription Empty { get; } = new PvpDescription(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}