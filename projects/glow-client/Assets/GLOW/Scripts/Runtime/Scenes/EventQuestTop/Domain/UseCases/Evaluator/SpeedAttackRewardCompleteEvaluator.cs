using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public static class SpeedAttackRewardCompleteEvaluator
    {

        public static StageRewardCompleteFlag Evaluate(SpeedAttackUseCaseModel speedAttackUseCaseModel)
        {
            if(speedAttackUseCaseModel.IsEmpty) return new StageRewardCompleteFlag(false);

            var speedAttackRewardComplete = speedAttackUseCaseModel.NextGoalTime.IsEmpty();
            return new StageRewardCompleteFlag(speedAttackRewardComplete);
        }
    }
}
