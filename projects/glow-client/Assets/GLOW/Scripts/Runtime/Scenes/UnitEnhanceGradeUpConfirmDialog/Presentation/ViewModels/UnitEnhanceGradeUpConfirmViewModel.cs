using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.ViewModels
{
    public record UnitEnhanceGradeUpConfirmViewModel(
        CharacterUnitRoleType RoleType,
        ItemIconViewModel Item,
        UnitGrade BeforeGrade,
        UnitGrade AfterGrade,
        ItemAmount PossessionAmount,
        HP BeforeHp,
        HP AfterHp,
        AttackPower BeforeAttackPower,
        AttackPower AfterAttackPower,
        SpecialAttackName SpecialAttackName,
        SpecialAttackInfoDescription Description,
        UnitGradeUpEnableConfirm EnableConfirm);
}
