using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpUserName(ObscuredString Value)
    {
        public static PvpUserName Empty { get; } = new PvpUserName(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}