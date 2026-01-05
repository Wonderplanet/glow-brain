using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Evaluator
{
    public interface ISelectedStageEvaluator
    {
        SelectedStageModel GetSelectedStage();
    }
}
