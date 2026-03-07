using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkGradeUpAvailableFlag(ObscuredBool Value)
    {
        public static ArtworkGradeUpAvailableFlag True { get; } =
            new ArtworkGradeUpAvailableFlag(true);

        public static ArtworkGradeUpAvailableFlag False { get; } =
            new ArtworkGradeUpAvailableFlag(false);

        public static implicit operator bool(ArtworkGradeUpAvailableFlag availableFlag) => availableFlag.Value;
    }
}
