using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceUnitStatusViewModel(
        CharacterUnitRoleType RoleType,
        HP Hp,
        AttackPower AttackPower,
        CharacterAttackRangeType AttackRange,
        UnitMoveSpeed MoveSpeed);
}
