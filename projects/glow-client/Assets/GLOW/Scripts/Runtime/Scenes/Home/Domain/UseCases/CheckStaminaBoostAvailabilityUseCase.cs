using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class CheckStaminaBoostAvailabilityUseCase
    {
        [Inject] IStaminaBoostEvaluator StaminaBoostEvaluator { get; }

        public StaminaBoostFlag CheckStaminaBoostAvailability(MasterDataId stageId)
        {
            return StaminaBoostEvaluator.HasStaminaBoost(stageId);
        }
    }
}
