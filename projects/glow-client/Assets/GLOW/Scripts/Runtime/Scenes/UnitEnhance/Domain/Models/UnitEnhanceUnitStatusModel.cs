using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceUnitStatusModel(
        HP Hp,
        AttackPower AttackPower,
        CharacterAttackRangeType AttackRange,
        UnitMoveSpeed MoveSpeed);
}
