using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record DisplayExpirationFlag(ObscuredBool Value)
    {
        public static DisplayExpirationFlag True { get; } = new(true);
        public static DisplayExpirationFlag False { get; } = new(false);
        
        public static implicit operator bool(DisplayExpirationFlag value) => value.Value;
    }
}