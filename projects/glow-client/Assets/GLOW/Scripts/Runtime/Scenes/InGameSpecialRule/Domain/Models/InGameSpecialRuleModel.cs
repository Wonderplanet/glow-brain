using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
namespace GLOW.Scenes.InGameSpecialRule.Domain.Models
{
    public record InGameSpecialRuleModel(
        List<SeriesLogoImagePath> SeriesLogoImagePathList,
        List<Rarity> UnitRarities,
        List<CharacterUnitRoleType> UnitRoleTypes,
        List<CharacterColor> CharacterColors,
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
        InGameSpecialRuleExistFormationRuleFlag ExistsFormationRule,
        InGameSpecialRuleExistOtherRuleFlag ExistsOtherRule)
    {
        public static InGameSpecialRuleModel Empty { get; } = new (
            new List<SeriesLogoImagePath>(),
            new List<Rarity>(),
            new List<CharacterUnitRoleType>(),
            new List<CharacterColor>(),
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
            InGameSpecialRuleExistFormationRuleFlag.False,
            InGameSpecialRuleExistOtherRuleFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
