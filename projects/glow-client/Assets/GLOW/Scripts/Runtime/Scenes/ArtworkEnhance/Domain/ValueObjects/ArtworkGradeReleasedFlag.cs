using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkGradeReleasedFlag(ObscuredBool Value)
    {
        public static ArtworkGradeReleasedFlag True { get; } = new ArtworkGradeReleasedFlag(true);
        public static ArtworkGradeReleasedFlag False { get; } = new ArtworkGradeReleasedFlag(false);

        public static implicit operator bool(ArtworkGradeReleasedFlag completedFlag) => completedFlag.Value;
    }
}
