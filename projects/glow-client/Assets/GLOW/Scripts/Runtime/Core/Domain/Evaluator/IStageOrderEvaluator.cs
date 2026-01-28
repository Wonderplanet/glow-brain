using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Evaluator
{
    public interface IStageOrderEvaluator
    {
        MstStageModel GetMaxOrderStage(IReadOnlyList<MstStageModel> mstStageModels);
        MstStageModel GetMaxOrderClearedStage();
        MstStageModel GetMaxOrderClearedStageWithNormalDifficulty();
    }
}