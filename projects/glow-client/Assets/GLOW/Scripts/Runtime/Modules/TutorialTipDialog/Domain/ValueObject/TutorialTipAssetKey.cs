using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Modules.TutorialTipDialog.Domain.ValueObject
{
    public record TutorialTipAssetKey(ObscuredString Value)
    {
        public static TutorialTipAssetKey Empty { get; } = new(string.Empty);
    }
}
