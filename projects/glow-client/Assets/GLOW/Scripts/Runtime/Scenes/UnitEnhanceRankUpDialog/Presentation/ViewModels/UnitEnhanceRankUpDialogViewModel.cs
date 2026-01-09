using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.ViewModels
{
    public record UnitEnhanceRankUpDialogViewModel(
        CharacterStandImageAssetPath AssetPath,
        CharacterUnitRoleType RoleType,
        UnitLevel BeforeLimitLevel,
        UnitLevel AfterLimitLevel,
        HP BeforeHP,
        HP AfterHP,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        IReadOnlyList<UnitEnhanceAbilityViewModel> NewlyAbilityViewModels);
}
