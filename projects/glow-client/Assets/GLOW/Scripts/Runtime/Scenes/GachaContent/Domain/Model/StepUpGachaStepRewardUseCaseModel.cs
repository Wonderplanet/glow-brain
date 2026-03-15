using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Domain.Model
{
    public record StepUpGachaStepRewardUseCaseModel(
        StepUpGachaLoopCountTarget LoopCountTarget,
        PlayerResourceModel PlayerResourceModel)
    {
        public static StepUpGachaStepRewardUseCaseModel Empty { get; } = new(
            StepUpGachaLoopCountTarget.Empty,
            PlayerResourceModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

