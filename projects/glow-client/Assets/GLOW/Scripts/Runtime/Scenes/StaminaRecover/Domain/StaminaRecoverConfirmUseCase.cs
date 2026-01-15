using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public class StaminaRecoverConfirmUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }

        public StaminaRecoverConfirmUseCaseModel GetModel()
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var paidDiamond = userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var consumeAmount = new TotalDiamond(MstConfigRepository.GetConfig(MstConfigKey.BuyStaminaDiamondAmount).Value.ToInt());
            var isShortage = (paidDiamond.Value + userParameterModel.FreeDiamond.Value) < consumeAmount.Value;
            var userStaminaModel = UserStaminaModelFactory.Create();

            return new StaminaRecoverConfirmUseCaseModel(
                isShortage,
                paidDiamond,
                userParameterModel.FreeDiamond,
                userStaminaModel.DiamondRecoverStaminaAmount,
                consumeAmount
                );
        }
    }
}
