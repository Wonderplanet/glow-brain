using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpDummyUserId(ObscuredString Value)
    {
        public static PvpDummyUserId Empty { get; } = new PvpDummyUserId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}