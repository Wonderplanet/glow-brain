using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models
{
    public record UnitEnhanceRankUpConfirmModel(
        IReadOnlyList<UnitEnhanceCostItemModel> CostItems,
        CharacterUnitRoleType RoleType,
        UnitLevel BeforeLimitLevel,
        UnitLevel AfterLimitLevel,
        HP BeforeHp,
        HP AfterHp,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        IReadOnlyList<UnitAbility> NewlyUnlockedUnitAbilities,
        UnitRankUpEnableConfirm UnitRankUpEnableConfirm);
}
