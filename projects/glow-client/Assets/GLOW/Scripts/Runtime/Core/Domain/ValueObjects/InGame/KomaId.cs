using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaId(ObscuredString Value)
    {
        public static KomaId Empty { get; } = new KomaId("0");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
