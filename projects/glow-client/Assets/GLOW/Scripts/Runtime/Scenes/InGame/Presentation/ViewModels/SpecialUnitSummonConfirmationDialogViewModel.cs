using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Presentation.ViewModels
{
    public record SpecialUnitSummonConfirmationDialogViewModel(
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription SpecialAttackInfoDescription,
        BattlePoint SummonCost,
        NeedTargetSelectTypeFlag NeedTargetSelectTypeFlag)
    {
        public static SpecialUnitSummonConfirmationDialogViewModel Empty { get; } = new (
            SpecialAttackName.Empty,
            SpecialAttackInfoDescription.Empty,
            BattlePoint.Empty,
            NeedTargetSelectTypeFlag.False);
    }
}
