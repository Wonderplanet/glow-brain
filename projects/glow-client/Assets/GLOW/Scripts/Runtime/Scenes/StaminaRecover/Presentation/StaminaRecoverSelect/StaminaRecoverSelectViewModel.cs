using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect
{
    public record StaminaRecoverSelectViewModel
    (
        string HeaderTitle,
        string Description,
        StaminaRecoveryFlag IsAdStaminaRecoverable,
        BuyStaminaAdCount RemainingAdRecoverCount,
        Stamina AdvRecoverStaminaValue,
        TotalDiamond ConsumeDiamondValue,
        Stamina DiamondRecoverStaminaValue,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel
    );
}
