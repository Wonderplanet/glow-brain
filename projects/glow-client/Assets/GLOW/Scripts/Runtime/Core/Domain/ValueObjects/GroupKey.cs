using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GroupKey(ObscuredString Value)
    {
        public static GroupKey Empty { get; } = new(string.Empty);
    }
}