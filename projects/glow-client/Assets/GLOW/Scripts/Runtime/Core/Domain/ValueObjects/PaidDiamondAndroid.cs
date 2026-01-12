using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PaidDiamondAndroid(ObscuredInt Value)
    {
        public static PaidDiamondAndroid Empty { get; } = new (0);
    };
}
