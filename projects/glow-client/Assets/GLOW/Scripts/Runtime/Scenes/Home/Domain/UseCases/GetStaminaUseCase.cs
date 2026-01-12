using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class GetStaminaUseCase
    {
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }
        
        public HomeUserStaminaUseCaseModel GetUserStamina()
        {
            var userStaminaModel = UserStaminaModelFactory.Create();

            return new HomeUserStaminaUseCaseModel(
                userStaminaModel.CurrentStamina, 
                userStaminaModel.MaxStamina, 
                userStaminaModel.RemainFullRecoverySeconds,
                userStaminaModel.RemainUpdatingStaminaRecoverSeconds,
                userStaminaModel.IsHeldAdditionalStaminaPassEffect);
        }
    }
}
