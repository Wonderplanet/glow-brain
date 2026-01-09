using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.Models
{
    public record UnitEnhanceRankUpDetailCellModel(
        CharacterUnitRoleType RoleType,
        UnitRank Rank,
        UnitLevel LimitLevel,
        UnitLevel RequiredLevel,
        IReadOnlyList<UnitEnhanceRequireItemModel> RequireItems,
        HP Hp,
        HP AddHp,
        AttackPower AttackPower,
        AttackPower AddAttackPower,
        IReadOnlyList<UnitAbility> NewlyUnlockedAbilities,
        bool IsComplete,
        bool IsLocked);
}
