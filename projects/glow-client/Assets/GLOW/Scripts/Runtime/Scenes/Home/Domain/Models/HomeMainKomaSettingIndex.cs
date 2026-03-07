using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaSettingIndex(ObscuredInt Value)
    {
        public static HomeMainKomaSettingIndex Empty { get; } = new(0);
    };
}
