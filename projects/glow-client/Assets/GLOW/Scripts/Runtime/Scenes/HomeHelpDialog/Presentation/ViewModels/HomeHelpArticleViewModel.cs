using GLOW.Scenes.HomeHelpDialog.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels
{
    public record HomeHelpArticleViewModel(
        HomeHelpArticleType Type,
        ObscuredString Text
        );
}
