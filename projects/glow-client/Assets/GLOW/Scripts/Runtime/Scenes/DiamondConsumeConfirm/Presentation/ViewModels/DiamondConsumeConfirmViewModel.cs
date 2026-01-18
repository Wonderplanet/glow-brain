using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.DiamondConsumeConfirm.Domain.ValueObjects;

namespace GLOW.Scenes.DiamondConsumeConfirm.Presentation.ViewModels
{
    public record DiamondConsumeConfirmViewModel(
        DiamondConsumeConfirmText Text,
        PaidDiamond CurrentPaidDiamond,
        PaidDiamond AfterPaidDiamond,
        FreeDiamond CurrentFreeDiamond,
        FreeDiamond AfterFreeDiamond,
        EnoughCostFlag IsEnoughDiamond);
}
