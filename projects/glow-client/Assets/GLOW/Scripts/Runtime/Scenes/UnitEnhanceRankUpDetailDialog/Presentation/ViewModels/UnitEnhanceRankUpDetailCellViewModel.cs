using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels
{
    public record UnitEnhanceRankUpDetailCellViewModel(
        CharacterUnitRoleType RoleType,
        UnitLevel LimitLevel,
        UnitLevel RequiredLevel,
        IReadOnlyList<UnitEnhanceRequireItemViewModel> RequireItems,
        HP Hp,
        HP AddHp,
        AttackPower AttackPower,
        AttackPower AddAttackPower,
        IReadOnlyList<UnitEnhanceAbilityViewModel> AbilityViewModels,
        bool IsComplete);
}
