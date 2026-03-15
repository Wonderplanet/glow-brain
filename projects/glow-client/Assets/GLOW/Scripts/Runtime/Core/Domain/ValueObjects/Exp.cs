using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record Exp(ObscuredInt Value)
    {
        public static Exp Empty { get; } = new(0);
    };
}
