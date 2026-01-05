using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail
{
    public record InGameUnitDetailSpecialAttackModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime CoolTime);
}
