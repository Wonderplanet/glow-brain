using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class GetUserMaxStaminaUseCase
    {
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public Stamina GetUserMaxStamina()
        {
            var maxStaminaAmount = MstConfigRepository.GetConfig(MstConfigKey.UserMaxStaminaAmount);
            if(maxStaminaAmount.IsEmpty()) return Stamina.Empty;

            return new Stamina(maxStaminaAmount.Value.ToInt());
        }
    }
}
