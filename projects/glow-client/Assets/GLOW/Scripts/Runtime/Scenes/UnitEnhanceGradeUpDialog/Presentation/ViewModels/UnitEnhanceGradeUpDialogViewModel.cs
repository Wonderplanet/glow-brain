using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.ViewModels
{
    public record UnitEnhanceGradeUpDialogViewModel(
        CharacterUnitRoleType RoleType,
        CharacterStandImageAssetPath AssetPath,
        UnitGrade BeforeGrade,
        UnitGrade AfterGrade,
        HP BeforeHP,
        HP AfterHP,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription Description);
}
