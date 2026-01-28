using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EmblemDescription(ObscuredString Value)
    {
        public static EmblemDescription Empty { get; } = new(string.Empty);
    }
}
