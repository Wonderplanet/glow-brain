using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class CalculateReceivableStaminaTimeUseCase
    {
        [Inject] IUserStaminaModelFactory StaminaModelFactory { get; }
        
        public ReceivableStaminaTimeModel CalcReceivableTime()
        {
            var userStaminaModel = StaminaModelFactory.Create();

            return new ReceivableStaminaTimeModel(
                userStaminaModel.CanRecoverByAd,
                userStaminaModel.RemainingAdRecoverTime);
        }
    }
}