using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkFormation.Domain.ValueObjects
{
    public record ArtworkGrayOutFlag(ObscuredBool Value)
    {
        public static ArtworkGrayOutFlag True { get; } = new ArtworkGrayOutFlag(true);
        public static ArtworkGrayOutFlag False { get; } = new ArtworkGrayOutFlag(false);

        public static implicit operator bool(ArtworkGrayOutFlag flag)
        {
            return flag.Value;
        }
    }
}

