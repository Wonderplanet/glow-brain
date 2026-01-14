using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using UnityEngine;
using Zenject;

namespace GLOW.Core.Domain.ModelFactories
{
    public class SpecialRoleSpecialAttackFactory : ISpecialRoleSpecialAttackFactory
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }

        public AttackData CreateSpecialRoleSpecialAttack(
            MstCharacterModel mstUnit,
            UserUnitModel userUnit)
        {
            var mstMaxLevel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstUnit.UnitLabel);
            var specialAttack = mstUnit.GetSpecialAttack(userUnit.Grade);
            
            return CreateSpecialRoleSpecialAttack(specialAttack, userUnit.Level, mstMaxLevel.Level);
        }
        public AttackData CreateSpecialRoleSpecialAttack(
            MstCharacterModel mstUnit,
            UnitGrade unitGrade, 
            UnitLevel currentLevel)
        {
            var mstMaxLevel = MstUnitLevelUpRepository.GetUnitMaxLevelUp(mstUnit.UnitLabel);
            var specialAttack = mstUnit.GetSpecialAttack(unitGrade);
            
            return CreateSpecialRoleSpecialAttack(specialAttack, currentLevel, mstMaxLevel.Level);
        }
        
        public AttackData CreateSpecialRoleSpecialAttack(
            MstSpecialAttackModel mstSpecialAttack,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            var updatedAttackElements = mstSpecialAttack.AttackData.AttackElements
                .Select(element => CalculateSpecialRoleSpecialAttackElement(
                    element,
                    mstSpecialAttack.SpecialRoleLevelUpAttackElements,
                    currentLevel,
                    maxLevel))
                .ToList();

            return mstSpecialAttack.AttackData with
            {
                AttackElements = updatedAttackElements
            };
        }

        AttackElement CalculateSpecialRoleSpecialAttackElement(
            AttackElement baseAttackElement,
            IReadOnlyList<SpecialRoleLevelUpAttackElement> specialRoleLevelUpAttackElements,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            // SubElementの置き換え
            var updateAttackSubElements = CalculateSpecialRoleSpecialAttackSubElements(
                baseAttackElement.SubElements,
                specialRoleLevelUpAttackElements,
                currentLevel,
                maxLevel);
            
            // メインのElementの置き換え
            var stateEffect = baseAttackElement.StateEffect;
            var powerParameter = baseAttackElement.PowerParameter;
            
            var specialRoleLevelUpAttackElement = specialRoleLevelUpAttackElements
                .FirstOrDefault(
                    e => e.MstAttackElementId == baseAttackElement.Id,
                    SpecialRoleLevelUpAttackElement.Empty);
            
            if (!specialRoleLevelUpAttackElement.IsEmpty())
            {
                stateEffect = CalculateStateEffect(
                    baseAttackElement.StateEffect,
                    specialRoleLevelUpAttackElement,
                    currentLevel,
                    maxLevel);

                powerParameter = CalculatePowerParameter(
                    baseAttackElement.PowerParameter,
                    specialRoleLevelUpAttackElement.MinAttackPowerParameter,
                    specialRoleLevelUpAttackElement.MaxAttackPowerParameter,
                    currentLevel,
                    maxLevel);
            }

            return baseAttackElement with
            {
                StateEffect = stateEffect,
                PowerParameter = powerParameter,
                SubElements = updateAttackSubElements
            };
        }

        IReadOnlyList<AttackSubElement> CalculateSpecialRoleSpecialAttackSubElements(
            IReadOnlyList<AttackSubElement> attackSubElements,
            IReadOnlyList<SpecialRoleLevelUpAttackElement> specialRoleLevelUpAttackElements,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            var updateAttackSubElements = attackSubElements.ToList();
            
            for (int i = 0; i < updateAttackSubElements.Count; i++)
            {
                var subElement = updateAttackSubElements[i];
                
                var specialRoleLevelUpAttackElement = specialRoleLevelUpAttackElements
                    .FirstOrDefault(
                        e => e.MstAttackElementId == subElement.Id,
                        SpecialRoleLevelUpAttackElement.Empty);
                
                if (specialRoleLevelUpAttackElement.IsEmpty()) continue;

                var stateEffect = CalculateStateEffect(
                    subElement.StateEffect,
                    specialRoleLevelUpAttackElement,
                    currentLevel,
                    maxLevel);

                var powerParameter = CalculatePowerParameter(
                    subElement.PowerParameter,
                    specialRoleLevelUpAttackElement.MinAttackPowerParameter,
                    specialRoleLevelUpAttackElement.MaxAttackPowerParameter,
                    currentLevel,
                    maxLevel);

                var newSubElement = subElement with
                {
                    StateEffect = stateEffect,
                    PowerParameter = powerParameter
                };

                updateAttackSubElements[i] = newSubElement;
            }

            return updateAttackSubElements;
        }

        StateEffect CalculateStateEffect(
            StateEffect baseStateEffect,
            SpecialRoleLevelUpAttackElement specialRoleLevelUpAttackElement,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            var effectiveCount = CalculateSpecialRoleSpecialAttackEffectiveCount(
                baseStateEffect.EffectiveCount,
                specialRoleLevelUpAttackElement.MinEffectiveCount,
                specialRoleLevelUpAttackElement.MaxEffectiveCount,
                currentLevel,
                maxLevel);

            var duration = CalculateSpecialRoleSpecialAttackDuration(
                baseStateEffect.Duration,
                specialRoleLevelUpAttackElement.MinStateEffectDuration,
                specialRoleLevelUpAttackElement.MaxStateEffectDuration,
                currentLevel,
                maxLevel);

            var parameter = CalculateStateEffectParameter(
                baseStateEffect.Parameter,
                specialRoleLevelUpAttackElement.MinStateEffectParameter,
                specialRoleLevelUpAttackElement.MaxStateEffectParameter,
                currentLevel,
                maxLevel);

            return baseStateEffect with
            {
                EffectiveCount = effectiveCount,
                Duration = duration,
                Parameter = parameter
            };
        }

        EffectiveCount CalculateSpecialRoleSpecialAttackEffectiveCount(
            EffectiveCount defaultEffectiveCount,
            EffectiveCount minEffectiveCount,
            EffectiveCount maxEffectiveCount,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            float perLevel = (maxEffectiveCount - minEffectiveCount).Value / (maxLevel - UnitLevel.One).Value;
            float constantTerm = minEffectiveCount.Value - (UnitLevel.One.Value * perLevel);
            float calculateValue = perLevel * currentLevel.Value + constantTerm;

            var floorValue = Mathf.FloorToInt(calculateValue);
            return defaultEffectiveCount + floorValue;
        }

        TickCount CalculateSpecialRoleSpecialAttackDuration(
            TickCount defaultDuration,
            TickCount minDuration,
            TickCount maxDuration,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            float perLevel = (maxDuration - minDuration).Value / (maxLevel - UnitLevel.One).Value;
            float constantTerm = minDuration.Value - (UnitLevel.One.Value * perLevel);
            float calculateValue = perLevel * currentLevel.Value + constantTerm;

            var floorValue = new TickCount(Mathf.FloorToInt(calculateValue));
            return defaultDuration + floorValue;
        }

        StateEffectParameter CalculateStateEffectParameter(
            StateEffectParameter defaultParameter,
            StateEffectParameter minParameter,
            StateEffectParameter maxParameter,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            decimal perLevel = (maxParameter - minParameter).Value / (maxLevel - UnitLevel.One).Value;
            decimal constantTerm = minParameter.Value - (UnitLevel.One.Value * perLevel);
            decimal calculateValue = perLevel * currentLevel.Value + constantTerm;

            calculateValue = calculateValue * 100;
            var floorValue = new StateEffectParameter(decimal.Floor(calculateValue) / 100);
            return defaultParameter + floorValue;
        }

        AttackPowerParameter CalculatePowerParameter(
            AttackPowerParameter defaultParameter,
            AttackPowerParameterValue minParameter,
            AttackPowerParameterValue maxParameter,
            UnitLevel currentLevel,
            UnitLevel maxLevel)
        {
            float maxParameterValue = maxParameter.Value;
            float minParameterValue = minParameter.Value;
            decimal maxParameterValueDecimal = (decimal)maxParameterValue;
            decimal minParameterValueDecimal = (decimal)minParameterValue;

            decimal perLevel = (maxParameterValueDecimal - minParameterValueDecimal) / (maxLevel - UnitLevel.One).Value;
            decimal constantTerm = minParameterValueDecimal - UnitLevel.One.Value * perLevel;
            decimal calculateValue = perLevel * currentLevel.Value + constantTerm;

            calculateValue = calculateValue * 100;
            var floorValue = (float)(decimal.Floor(calculateValue) / 100);

            return defaultParameter with
            {
                Value = defaultParameter.Value + floorValue
            };
        }
    }
}