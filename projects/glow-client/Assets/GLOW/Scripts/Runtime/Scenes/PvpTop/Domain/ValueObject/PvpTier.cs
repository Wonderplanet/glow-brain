using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpTier(ObscuredInt Value)
    {
        public static PvpTier Zero { get; } = new PvpTier(0);
        public static PvpTier Max { get; } = new PvpTier(4);
    };
}
