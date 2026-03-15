using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm
{
    public record StaminaDiamondRecoverConfirmViewModel(
        bool IsShortage,
        TotalDiamond ConsumeDiamond,
        Stamina RecoverValue,
        PaidDiamond BeforePaidDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond BeforeFreeDiamond,
        FreeDiamond AfterFreeDiamond);
}
