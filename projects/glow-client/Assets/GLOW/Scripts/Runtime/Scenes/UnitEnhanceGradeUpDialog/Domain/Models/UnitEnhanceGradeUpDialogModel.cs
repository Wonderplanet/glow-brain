using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.Models
{
    public record UnitEnhanceGradeUpDialogModel(
        CharacterUnitRoleType RoleType,
        UnitAssetKey AssetKey,
        HP BeforeHP,
        HP AfterHP,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription Description,
        EncyclopediaRewardConditionAchievedFlag IsEncyclopediaRewardConditionAchieved);
}
