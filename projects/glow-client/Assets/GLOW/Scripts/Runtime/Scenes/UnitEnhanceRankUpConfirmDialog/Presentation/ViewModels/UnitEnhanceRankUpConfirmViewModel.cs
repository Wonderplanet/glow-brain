using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.ViewModels
{
    public record UnitEnhanceRankUpConfirmViewModel(
        IReadOnlyList<UnitEnhanceCostItemViewModel> CostItems,
        CharacterUnitRoleType RoleType,
        UnitLevel BeforeLimitLevel,
        UnitLevel AfterLimitLevel,
        HP BeforeHp,
        HP AfterHp,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        IReadOnlyList<UnitEnhanceAbilityViewModel> UnitAbilities,
        UnitRankUpEnableConfirm EnableConfirm);
}
