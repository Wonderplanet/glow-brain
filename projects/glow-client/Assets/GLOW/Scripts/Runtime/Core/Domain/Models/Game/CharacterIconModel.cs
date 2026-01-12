using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record CharacterIconModel(
        CharacterIconAssetPath IconAssetPath,
        CharacterUnitRoleType Role,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        BattlePoint SummonCost,
        UnitGrade Grade,
        HP Hp,
        AttackPower AttackPower,
        CharacterAttackRangeType AttackRangeType,
        UnitMoveSpeed MoveSpeed)
    {
        public static CharacterIconModel Empty { get; } = new(
            CharacterIconAssetPath.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            Rarity.R,
            UnitLevel.Empty,
            BattlePoint.Empty,
            UnitGrade.Empty,
            HP.Empty,
            AttackPower.Empty,
            CharacterAttackRangeType.Short,
            UnitMoveSpeed.Empty);
    }
}
