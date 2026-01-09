using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public record StaminaRecoverConfirmUseCaseModel( bool IsShortage,
        PaidDiamond PaidDiamond, FreeDiamond FreeDiamond, Stamina RecoverValue, TotalDiamond ConsumeDiamondValue);
}
