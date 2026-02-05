using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitList.Domain.ValueObjects;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public record UnitSortFilterCategoryModel(
        FilterColorModel FilterColorModel,
        FilterRoleModel FilterRoleModel,
        FilterAttackRangeModel FilterAttackRangeModel,
        FilterRarityModel FilterRarityModel,
        FilterSpecialAttackModel FilterSpecialAttackModel,
        FilterAbilityModel FilterAbilityModel,
        FilterSeriesModel FilterSeriesModel,
        FilterBonusModel FilterBonusModel,
        FilterFormationModel FilterFormationModel,
        UnitListSortType SortType,
        UnitListSortOrder SortOrder)
    {
        public static UnitSortFilterCategoryModel Default { get; } = new UnitSortFilterCategoryModel(
            FilterColorModel.Default,
            FilterRoleModel.Default,
            FilterAttackRangeModel.Default,
            FilterRarityModel.Default,
            FilterSpecialAttackModel.Default,
            FilterAbilityModel.Default,
            FilterSeriesModel.Default,
            FilterBonusModel.Default,
            FilterFormationModel.Default,
            UnitListSortType.Rarity,
            UnitListSortOrder.Descending);

        public bool IsAnyFilter()
        {
            return FilterColorModel.IsAnyFilter ||
                   FilterRoleModel.IsAnyFilter ||
                   FilterAttackRangeModel.IsAnyFilter ||
                   FilterRarityModel.IsAnyFilter ||
                   FilterSpecialAttackModel.IsAnyFilter ||
                   FilterAbilityModel.IsAnyFilter ||
                   FilterSeriesModel.IsAnyFilter ||
                   FilterBonusModel.IsAnyFilter ||
                   FilterFormationModel.IsAnyFilter;
        }

        public bool EqualsColor(CharacterColor color)
        {
            if (!FilterColorModel.IsAnyFilter)
            {
                return true;
            }

            return FilterColorModel.FilterColors.Contains(color);
        }

        public bool EqualsRole(CharacterUnitRoleType role)
        {
            if (!FilterRoleModel.IsAnyFilter)
            {
                return true;
            }

            return FilterRoleModel.FilterRoles.Contains(role);
        }

        public bool EqualsAttackRange(CharacterAttackRangeType attackRange)
        {
            if (!FilterAttackRangeModel.IsAnyFilter)
            {
                return true;
            }

            return FilterAttackRangeModel.AttackRangeTypes.Contains(attackRange);
        }

        public bool EqualsRarity(Rarity rarity)
        {
            if (!FilterRarityModel.IsAnyFilter)
            {
                return true;
            }

            return FilterRarityModel.Rarities.Contains(rarity);
        }

        public bool EqualsSpecialAttack(AttackData attackData)
        {
            if (!FilterSpecialAttackModel.IsAnyFilter)
            {
                return true;
            }

            foreach (var filterSpecialAttack in FilterSpecialAttackModel.SpecialAttacks)
            {
                bool isMatch = filterSpecialAttack switch
                {
                    FilterSpecialAttack.KillerRed => HasKillerColor(attackData, CharacterColor.Red),
                    FilterSpecialAttack.KillerBlue => HasKillerColor(attackData, CharacterColor.Blue),
                    FilterSpecialAttack.KillerGreen => HasKillerColor(attackData, CharacterColor.Green),
                    FilterSpecialAttack.KillerYellow => HasKillerColor(attackData, CharacterColor.Yellow),
                    FilterSpecialAttack.KnockBack => IsKnockBack(attackData),
                    FilterSpecialAttack.Drain => HasAttackHitType(attackData, AttackHitType.Drain),
                    FilterSpecialAttack.Stun => HasAttackHitType(attackData, AttackHitType.Stun),
                    FilterSpecialAttack.Freeze => HasAttackHitType(attackData, AttackHitType.Freeze),
                    FilterSpecialAttack.Burn => HasStateEffectType(attackData, StateEffectType.Burn),
                    FilterSpecialAttack.Poison => HasStateEffectType(attackData, StateEffectType.Poison),
                    FilterSpecialAttack.Weakening => HasStateEffectType(attackData, StateEffectType.Weakening),
                    FilterSpecialAttack.StatusUp => IsStatusUp(attackData),
                    FilterSpecialAttack.StatusDown => IsStatusDown(attackData),
                    FilterSpecialAttack.DamageCut => IsDamageCut(attackData),
                    FilterSpecialAttack.Heal => IsHeal(attackData) || IsReGeneration(attackData),
                    FilterSpecialAttack.RushAttackPowerUp =>
                        HasStateEffectType(attackData, StateEffectType.RushAttackPowerUp),
                    FilterSpecialAttack.PlacedItem => IsPlaceItem(attackData),
                    FilterSpecialAttack.SpecialAttackCoolTimeShorten =>
                        HasStateEffectType(attackData, StateEffectType.SpecialAttackCoolTimeShorten),
                    FilterSpecialAttack.SummonCoolTimeShorten =>
                        HasStateEffectType(attackData, StateEffectType.SummonCoolTimeShorten),
                    _ => false
                };

                if (isMatch)
                {
                    return true;
                }
            }

            return false;
        }

        public bool EqualsAbility(List<MstUnitAbilityModel> unitAbilityModels)
        {
            if (!FilterAbilityModel.IsAnyFilter)
            {
                return true;
            }

            return unitAbilityModels.Any(ability => FilterAbilityModel.AbilityTypes.Contains(ability.UnitAbility.Type));
        }

        public bool EqualsSeries(MasterDataId seriesId)
        {
            if (!FilterSeriesModel.IsAnyFilter)
            {
                return true;
            }

            return FilterSeriesModel.SeriesIds.Contains(seriesId);
        }

        public bool EqualsBonus(PartyBonusUnitModel bonus)
        {
            if (FilterBonusModel.IsAnyFilter)
            {
                return !bonus.IsEmpty();
            }

            return true;
        }

        public bool EqualsFormation(
            FilterAchievedSpecialRuleFlag filterAchievedSpecialRuleFlag,
            FilterNotAchieveSpecialRuleFlag filterNotAchieveSpecialRuleFlag)
        {
            if (!FilterFormationModel.IsAnyFilter) return true;
            if (!FilterFormationModel.IsOn()) return true;
            if (!FilterFormationModel.IsFilterAchievedSpecialRuleFlag) return filterNotAchieveSpecialRuleFlag;
            if (!FilterFormationModel.IsFilterNotAchieveSpecialRuleFlag) return filterAchievedSpecialRuleFlag;

            return true;
        }

        bool HasKillerColor(AttackData attackData, CharacterColor targetColor)
        {
            return attackData.BaseData.KillerColors.Any(color => color == targetColor);
        }

        bool HasAttackHitType(AttackData attackData, AttackHitType targetHitType)
        {
            return attackData.AttackElements.Any(element =>
                element.AttackHitData.HitType == targetHitType ||
                element.SubElements.Any(subElement => subElement.AttackHitData.HitType == targetHitType));
        }

        bool HasStateEffectType(AttackData attackData, StateEffectType targetStateEffectType)
        {
            return attackData.AttackElements.Any(element =>
                element.StateEffect.Type == targetStateEffectType ||
                element.SubElements.Any(subElement => subElement.StateEffect.Type == targetStateEffectType));
        }

        bool IsKnockBack(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.AttackHitData.HitType.IsKnockBack() ||
                element.SubElements.Any(subElement => subElement.AttackHitData.HitType.IsKnockBack()));
        }

        bool IsStatusUp(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.StateEffect.Type.IsStatusUp() ||
                element.SubElements.Any(subElement => subElement.StateEffect.Type.IsStatusUp()));
        }

        bool IsStatusDown(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.StateEffect.Type.IsStatusDown() ||
                element.SubElements.Any(subElement => subElement.StateEffect.Type.IsStatusDown()));
        }

        bool IsDamageCut(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.StateEffect.Type.IsDamageCut() ||
                element.SubElements.Any(subElement => subElement.StateEffect.Type.IsDamageCut()));
        }

        bool IsHeal(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.AttackDamageType.IsHeal() ||
                element.SubElements.Any(subElement => subElement.AttackDamageType.IsHeal()));
        }

        /// <summary> 継続回復 </summary>
        bool IsReGeneration(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.StateEffect.Type.IsRegeneration() ||
                element.SubElements.Any(subElement => subElement.StateEffect.Type.IsRegeneration()));
        }

        bool IsPlaceItem(AttackData attackData)
        {
            return attackData.AttackElements.Any(element =>
                element.AttackType == AttackType.PlaceItem);
        }
    }
}
