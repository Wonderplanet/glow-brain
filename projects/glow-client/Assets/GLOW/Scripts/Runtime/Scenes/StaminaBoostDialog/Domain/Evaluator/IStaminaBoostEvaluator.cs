using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator
{
    public interface IStaminaBoostEvaluator
    {
        public StaminaBoostFlag HasStaminaBoost(MasterDataId stageId);
        public StaminaBoostFlag HasStaminaBoost(MstStageModel mstStageModel);
        public StaminaBoostCount GetStaminaBoostCountLimit(MstStageModel mstStageModel);
    }
}
