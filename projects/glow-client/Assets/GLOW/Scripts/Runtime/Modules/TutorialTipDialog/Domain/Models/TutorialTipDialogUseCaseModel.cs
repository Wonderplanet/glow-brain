using System.Collections.Generic;

namespace GLOW.Modules.TutorialTipDialog.Domain.Models
{
    public record TutorialTipDialogUseCaseModel(IReadOnlyList<TutorialTipModel> TutorialTipModels)
    {
        public static TutorialTipDialogUseCaseModel Empty { get; } =
            new TutorialTipDialogUseCaseModel(new List<TutorialTipModel>());
        
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
