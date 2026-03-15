using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record HomeMainKomaPatternName(ObscuredString Value)
    {
        public static HomeMainKomaPatternName Empty { get; } = new("");
    };
}
