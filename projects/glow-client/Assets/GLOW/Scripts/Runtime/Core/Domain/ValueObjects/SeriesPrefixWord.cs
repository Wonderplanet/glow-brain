using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesPrefixWord(ObscuredString Value)
    {
        public static SeriesPrefixWord Empty { get; } = new(string.Empty);
    }
}
