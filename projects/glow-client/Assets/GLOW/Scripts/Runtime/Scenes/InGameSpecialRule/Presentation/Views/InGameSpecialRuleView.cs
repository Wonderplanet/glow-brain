using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGameSpecialRule.Presentation.Views
{
    public class InGameSpecialRuleView : UIView
    {
        [SerializeField] InGameSpecialRuleComponent _specialRuleComponent;

        public void SetupEmpty()
        {
            _specialRuleComponent.SetupEmpty();
        }

        public void InitializeSpecialRule()
        {
            _specialRuleComponent.SetupEmpty();
        }

        public void SetupSeriesLogos(List<SeriesLogoImagePath> seriesLogoImagePathList)
        {
            _specialRuleComponent.SetupSeriesLogos(seriesLogoImagePathList);
        }

        public void SetupUnitRarity(List<Rarity> rarities)
        {
            _specialRuleComponent.SetupUnitRarity(rarities);
        }

        public void SetupUnitRoleType(List<CharacterUnitRoleType> roleTypes)
        {
            _specialRuleComponent.SetupUnitRoleType(roleTypes);
        }

        public void SetupUnitColor(List<CharacterColor> characterColors)
        {
            _specialRuleComponent.SetupUnitColor(characterColors);
        }

        public void SetupUnitCount(InGameSpecialRuleUnitAmount unitAmount)
        {
            _specialRuleComponent.SetupUnitCount(unitAmount);
        }

        public void SetupAttackRange(List<CharacterAttackRangeType> unitAttackRangeTypes)
        {
            _specialRuleComponent.SetupAttackRange(unitAttackRangeTypes);
        }

        public void SetupSpeedAttack(InGameSpecialRuleSpeedAttackFlag isSpeedAttack)
        {
            _specialRuleComponent.SetupSpeedAttack(isSpeedAttack);
        }

        public void SetupTimeLimit(InGameSpecialRuleTimeLimit timeLimit)
        {
            _specialRuleComponent.SetupTimeLimit(timeLimit);
        }

        public void SetupDefenseTarget(InGameSpecialRuleDefenseTargetFlag isDefenseTarget)
        {
            _specialRuleComponent.SetupDefenseTarget(isDefenseTarget);
        }

        public void SetupEnemyDestruction(
            DefeatEnemyCount count,
            InGameSpecialRuleEnemyDestructionFlag isEnemyDestruction)
        {
            _specialRuleComponent.SetupEnemyDestruction(count, isEnemyDestruction);
        }

        public void SetupSpecificEnemyDestruction(
            CharacterName enemyName,
            DefeatEnemyCount count,
            InGameSpecialRuleSpecificEnemyDestructionFlag isSpecificEnemyDestruction)
        {
            _specialRuleComponent.SetupSpecificEnemyDestruction(
                enemyName,
                count,
                isSpecificEnemyDestruction);
        }

        public void SetupStartOutpost(InGameSpecialRuleStartOutpostHp startOutpostHp, InGameSpecialRuleStartOutpostHpFlag isStartOutpostHp)
        {
            _specialRuleComponent.SetupStartOutpost(startOutpostHp, isStartOutpostHp);
        }

        public void SetupEnemyOutpostDamageInvalidation(InGameSpecialRuleEnemyOutpostDamageInvalidationFlag isEnemyOutpostDamageInvalidation)
        {
            _specialRuleComponent.SetupEnemyOutpostDamageInvalidation(isEnemyOutpostDamageInvalidation);
        }

        public void SetupNoContinue(InGameSpecialRuleNoContinueFlag isNoContinue)
        {
            _specialRuleComponent.SetupNoContinue(isNoContinue);
        }

        public void SetupHeaderComment(
            InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect,
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule)
        {
            _specialRuleComponent.SetupHeaderComment(isFromUnitSelect, existsFormationRule);
        }

        public void SetUpSpecialRuleHeader(
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule,
            InGameSpecialRuleExistOtherRuleFlag existsOtherRule)
        {
            _specialRuleComponent.SetUpSpecialRuleHeader(existsFormationRule, existsOtherRule);
        }
    }
}
