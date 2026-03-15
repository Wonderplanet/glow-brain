using System.Collections.Generic;
using GLOW.Scenes.GachaList.Domain.Model;

namespace GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha
{
    public record StepUpGachaViewModel(
        IReadOnlyList<StepUpGachaDetailViewModel> DetailViewModels,
        StepUpStepCount StepUpStepCount)
    {
        public static StepUpGachaViewModel Empty { get; } = 
            new StepUpGachaViewModel(new List<StepUpGachaDetailViewModel>(), StepUpStepCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}