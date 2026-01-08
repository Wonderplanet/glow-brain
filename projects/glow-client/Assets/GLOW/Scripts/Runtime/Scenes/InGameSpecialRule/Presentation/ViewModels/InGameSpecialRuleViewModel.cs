using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
namespace GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels
{
    public record InGameSpecialRuleViewModel(
        List<SeriesLogoImagePath> SeriesLogoImagePathList,
        List<Rarity> UnitRarities,
        List<CharacterUnitRoleType> UnitRoleTypes,
        InGameSpecialRuleUnitAmount UnitAmount,
        List<CharacterAttackRangeType> UnitAttackRangeTypes,
        DefeatEnemyCount EnemyDestructionCount,
        CharacterName SpecificEnemyDestructionTargetName,
        DefeatEnemyCount SpecificEnemyDestructionCount,
        InGameSpecialRuleStartOutpostHp StartOutpostHp,
        InGameSpecialRuleTimeLimit TimeLimit,
        InGameSpecialRuleDefenseTargetFlag IsDefenseTarget,
        InGameSpecialRuleEnemyDestructionFlag IsEnemyDestruction,
        InGameSpecialRuleSpecificEnemyDestructionFlag IsSpecificEnemyDestruction,
        InGameSpecialRuleStartOutpostHpFlag IsStartOutpostHp,
        InGameSpecialRuleEnemyOutpostDamageInvalidationFlag IsEnemyOutpostDamageInvalidation,
        InGameSpecialRuleNoContinueFlag IsNoContinue,
        InGameSpecialRuleSpeedAttackFlag IsSpeedAttack,
        InGameSpecialRuleFromUnitSelectFlag IsFromUnitSelect,
        InGameSpecialRuleExistFormationRuleFlag ExistsFormationRule,
        InGameSpecialRuleExistOtherRuleFlag ExistsOtherRule
        )
    {
        public static InGameSpecialRuleViewModel Empty { get; } = new (
            new List<SeriesLogoImagePath>(),
            new List<Rarity>(),
            new List<CharacterUnitRoleType>(),
            InGameSpecialRuleUnitAmount.Zero,
            new List<CharacterAttackRangeType>(),
            DefeatEnemyCount.Empty,
            CharacterName.Empty,
            DefeatEnemyCount.Empty,
            InGameSpecialRuleStartOutpostHp.Zero,
            InGameSpecialRuleTimeLimit.Empty,
            InGameSpecialRuleDefenseTargetFlag.False,
            InGameSpecialRuleEnemyDestructionFlag.False,
            InGameSpecialRuleSpecificEnemyDestructionFlag.False,
            InGameSpecialRuleStartOutpostHpFlag.False,
            InGameSpecialRuleEnemyOutpostDamageInvalidationFlag.False,
            InGameSpecialRuleNoContinueFlag.False,
            InGameSpecialRuleSpeedAttackFlag.False,
            InGameSpecialRuleFromUnitSelectFlag.False,
            InGameSpecialRuleExistFormationRuleFlag.False,
            InGameSpecialRuleExistOtherRuleFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
