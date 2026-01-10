using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public record StaminaRecoverSelectUseCaseModel(
        StaminaRecoveryFlag AdStaminaRecover,
        Stamina AdRecoverStamina,
        BuyStaminaAdCount RemainingAdRecoverCount,
        Stamina DiamondRecoverStamina,
        TotalDiamond ConsumeDiamondValue,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel);
}
