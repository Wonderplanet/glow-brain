using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkGradeMaxLimitFlag(ObscuredBool Value)
    {
        public static ArtworkGradeMaxLimitFlag False { get; } = new ArtworkGradeMaxLimitFlag(false);
        public static ArtworkGradeMaxLimitFlag True { get; } = new ArtworkGradeMaxLimitFlag(true);

        public static implicit operator bool(ArtworkGradeMaxLimitFlag gradeMaxLimitFlag) => gradeMaxLimitFlag.Value;
    }
}
