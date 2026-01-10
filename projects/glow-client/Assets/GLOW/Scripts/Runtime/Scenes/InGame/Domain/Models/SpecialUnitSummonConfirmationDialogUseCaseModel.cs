using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialUnitSummonConfirmationDialogUseCaseModel(
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription SpecialAttackInfoDescription,
        BattlePoint SummonCost,
        NeedTargetSelectTypeFlag NeedTargetSelectTypeFlag)
    {
        public static SpecialUnitSummonConfirmationDialogUseCaseModel Empty { get; } = new (
                SpecialAttackName.Empty,
                SpecialAttackInfoDescription.Empty,
                BattlePoint.Empty,
                NeedTargetSelectTypeFlag.False);
    }
}
