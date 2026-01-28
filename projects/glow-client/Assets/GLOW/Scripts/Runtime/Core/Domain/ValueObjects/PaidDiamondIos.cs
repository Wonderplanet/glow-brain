using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PaidDiamondIos(ObscuredInt Value)
    {
        public static PaidDiamondIos Empty { get; } = new (0);
    };
}
