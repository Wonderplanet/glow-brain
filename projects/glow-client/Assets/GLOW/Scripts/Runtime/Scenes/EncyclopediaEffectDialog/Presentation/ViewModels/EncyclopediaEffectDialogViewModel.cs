using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Presentation.ViewModels
{
    public record EncyclopediaEffectDialogViewModel(
        UnitEncyclopediaEffectValue AttackPower,
        UnitEncyclopediaEffectValue Hp,
        UnitEncyclopediaEffectValue Heal);
}
