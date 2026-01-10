using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Modules.TutorialTipDialog.Domain.ValueObject
{
    public record TutorialTipDialogTitle(ObscuredString Value)
    {
        public static TutorialTipDialogTitle Empty { get; } = new TutorialTipDialogTitle(string.Empty);
    }
}
