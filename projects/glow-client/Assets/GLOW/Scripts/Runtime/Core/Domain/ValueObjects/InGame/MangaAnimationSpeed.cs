using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationSpeed(ObscuredFloat Value)
    {
        public static MangaAnimationSpeed Empty { get; } = new MangaAnimationSpeed(1f);
        public static MangaAnimationSpeed Default { get; } = new MangaAnimationSpeed(1f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}