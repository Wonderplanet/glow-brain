using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;

namespace GLOW.Modules.TutorialTipDialog.Presentation.ViewModel
{
    public record TutorialTipDialogViewModel(
        TutorialTipDialogTitle Title,
        TutorialTipAssetPath AssetPath);
}
