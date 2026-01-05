using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;

namespace GLOW.Modules.TutorialTipDialog.Domain.Models
{
    public record TutorialTipModel(
        TutorialTipDialogTitle Title, 
        TutorialTipAssetPath AssetPath,
        ShouldShowNextButtonTextFlag ShouldShowNextButtonTextFlag)
    {
        public static TutorialTipModel Empty { get; } = new TutorialTipModel(
                TutorialTipDialogTitle.Empty, 
                TutorialTipAssetPath.Empty,
                ShouldShowNextButtonTextFlag.False);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
