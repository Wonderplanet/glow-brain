using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesName(ObscuredString Value)
    {
        public static SeriesName Empty { get; } = new(string.Empty);
    }
}
