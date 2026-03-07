using System.Collections.Generic;

namespace GLOW.Scenes.StepupGachaRatioDialog.Domain.Models
{
    public record StepupGachaRatioDialogUseCaseModel(
        IReadOnlyList<StepupGachaRatioStepUseCaseModel> StepUseCaseModels)
    {
        public static StepupGachaRatioDialogUseCaseModel Empty { get; } = new(
            new List<StepupGachaRatioStepUseCaseModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

