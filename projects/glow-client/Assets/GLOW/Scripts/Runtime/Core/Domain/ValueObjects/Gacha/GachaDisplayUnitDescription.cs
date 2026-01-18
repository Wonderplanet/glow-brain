using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaDisplayUnitDescription(ObscuredString Value)
    {
        public static GachaDisplayUnitDescription Empty { get; } = new("");
    };
}
