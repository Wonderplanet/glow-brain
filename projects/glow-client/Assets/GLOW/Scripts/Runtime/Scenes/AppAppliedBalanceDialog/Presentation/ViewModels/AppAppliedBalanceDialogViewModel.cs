using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AppAppliedBalanceDialog.Presentation
{
    public record AppAppliedBalanceDialogViewModel(FreeDiamond FreeDiamond, PaidDiamond PaidDiamond, TotalDiamond TotalDiamond)
    {
        public FreeDiamond FreeDiamond { get; } = FreeDiamond;
        public PaidDiamond PaidDiamond { get; } = PaidDiamond;
        public TotalDiamond TotalDiamond { get; } = TotalDiamond;
    }
}
