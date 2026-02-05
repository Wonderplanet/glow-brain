using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.Models
{
    public record UnitEnhanceGradeUpConfirmModel(
        CharacterUnitRoleType RoleType,
        ItemModel Item,
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
