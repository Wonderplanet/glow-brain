using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaBackgroundOffset(ObscuredFloat Value)
    {
        public static KomaBackgroundOffset Empty { get; } = new(0f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
