using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Domain.Models
{
    public record EncyclopediaEffectDialogUseCaseModel(
        UnitEncyclopediaEffectValue AttackPower,
        UnitEncyclopediaEffectValue Hp,
        UnitEncyclopediaEffectValue Heal);
}
