using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    public record ArtworkGradeUpItemEnoughFlag(ObscuredBool Value)
    {
        public static ArtworkGradeUpItemEnoughFlag True => new ArtworkGradeUpItemEnoughFlag(true);
        public static ArtworkGradeUpItemEnoughFlag False => new ArtworkGradeUpItemEnoughFlag(false);

        public static implicit operator bool(ArtworkGradeUpItemEnoughFlag flag) => flag.Value;
    }
}
