using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class StaminaRecoverSelectUseCase
    {
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUserStaminaModelFactory StaminaModelFactory { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }

        public StaminaRecoverSelectUseCaseModel GetModel()
        {
            var userStaminaModel = StaminaModelFactory.Create();

            var heldAdSkipPass = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();

            return new StaminaRecoverSelectUseCaseModel(
                userStaminaModel.CanRecoverByAd,
                userStaminaModel.AdRecoverStaminaAmount,
                userStaminaModel.RemainingAdRecoverCount,
                userStaminaModel.DiamondRecoverStaminaAmount,
                new TotalDiamond(MstConfigRepository.GetConfig(MstConfigKey.BuyStaminaDiamondAmount).Value.ToInt()),
                heldAdSkipPass
                );
        }
    }
}
