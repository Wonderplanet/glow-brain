using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record CheckUnitGradeUpArtworkRewardFlag(ObscuredBool Value)
    {
        public static CheckUnitGradeUpArtworkRewardFlag True { get; } = new(true);
        public static CheckUnitGradeUpArtworkRewardFlag False { get; } = new(false);

        public static implicit operator bool(CheckUnitGradeUpArtworkRewardFlag flag) => flag.Value;
    }
}
