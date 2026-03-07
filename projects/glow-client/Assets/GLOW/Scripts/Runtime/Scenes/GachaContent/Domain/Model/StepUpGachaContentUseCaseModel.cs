using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Domain.Model
{
    /// <summary>
    /// StepUpGachaComponent表示用のUseCaseModel
    /// 各ステップの詳細情報と現在のステップ情報を保持
    /// </summary>
    public record StepUpGachaContentUseCaseModel(
        IReadOnlyList<StepUpGachaStepUseCaseModel> Steps,
        StepUpGachaCurrentStepNumber UserCurrentStepNumber,
        StepUpGachaMaxStepNumber MaxStepNumber,
        StepUpGachaMaxLoopCount MaxLoopCount,
        StepUpGachaCurrentLoopCount CurrentLoopCount)
    {
        public static StepUpGachaContentUseCaseModel Empty { get; } = new(
            new List<StepUpGachaStepUseCaseModel>(),
            StepUpGachaCurrentStepNumber.Empty,
            StepUpGachaMaxStepNumber.Empty,
            StepUpGachaMaxLoopCount.Empty,
            StepUpGachaCurrentLoopCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}


