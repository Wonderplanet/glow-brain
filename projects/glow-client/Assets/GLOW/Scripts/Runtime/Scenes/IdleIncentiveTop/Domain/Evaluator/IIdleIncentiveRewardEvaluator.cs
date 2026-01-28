using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator
{
    public interface IIdleIncentiveRewardEvaluator
    {
        MstIdleIncentiveRewardModel EvaluateHighestClearedStageReward();
    }
}