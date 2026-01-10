using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects
{
    public record HomeHelpInfoAssetKey(ObscuredString Value)
    {
        public static HomeHelpInfoAssetKey Default => new ("home_help_info_list");
    }
}
