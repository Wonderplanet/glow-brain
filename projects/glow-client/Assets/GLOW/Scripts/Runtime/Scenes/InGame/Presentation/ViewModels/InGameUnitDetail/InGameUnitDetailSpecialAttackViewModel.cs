using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail
{
    public record InGameUnitDetailSpecialAttackViewModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime CoolTime);
}
