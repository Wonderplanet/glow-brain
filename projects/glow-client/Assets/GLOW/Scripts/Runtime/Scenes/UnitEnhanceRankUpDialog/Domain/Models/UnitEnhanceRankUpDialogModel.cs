using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Domain.Models
{
    public record UnitEnhanceRankUpDialogModel(
        UnitAssetKey AssetKey,
        CharacterUnitRoleType RoleType,
        UnitLevel BeforeLimitLevel,
        UnitLevel AfterLimitLevel,
        HP BeforeHP,
        HP AfterHP,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        IReadOnlyList<UnitEnhanceAbilityModel> NewlyAbilityModels);
}
