using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public class UnitListFilterAndSort : IUnitListFilterAndSort
    {
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] ISeriesPrefixWordSortHelper SeriesPrefixWordSortHelper { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }

        record FilterUnitModel(
            MstCharacterModel MstUnit,
            UserUnitModel UserUnit,
            PartyBonusUnitModel PartyBonusUnit,
            FilterAchievedSpecialRuleFlag IsAchievedSpecialRuleUnit,
            FilterNotAchieveSpecialRuleFlag IsNotAchieveSpecialRuleUnit);

        IReadOnlyList<UserUnitModel> IUnitListFilterAndSort.FilterAndSort(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var filterUnits = CreateFilterUnitModels(
                userUnits,
                mstUnits,
                partyBonusUnits,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds);

            var filteredUnits = Filter(filterUnits, sortFilterCategory, unitLevelUpModels);
            return Sort(filteredUnits, seriesModels, specialRuleUnitStatusModels, sortFilterCategory.SortType, sortFilterCategory.SortOrder);
        }

        IReadOnlyList<UserUnitModel> IUnitListFilterAndSort.Filter(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds)
        {
            var filterUnits = CreateFilterUnitModels(
                userUnits,
                mstUnits,
                partyBonusUnits,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds);

            return Filter(filterUnits, sortFilterCategory, unitLevelUpModels)
                .Select(model => model.UserUnit)
                .ToList();
        }

        IReadOnlyList<FilterUnitModel> Filter(
            IReadOnlyList<FilterUnitModel> unitModels,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels)
        {
            return unitModels
                .Where(model => sortFilterCategory.EqualsColor(model.MstUnit.Color))
                .Where(model => sortFilterCategory.EqualsRole(model.MstUnit.RoleType))
                .Where(model => sortFilterCategory.EqualsAttackRange(model.MstUnit.AttackRangeType))
                .Where(model => sortFilterCategory.EqualsRarity(model.MstUnit.Rarity))
                .Where(model =>
                {
                    var specialAttack = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                        model.MstUnit,
                        model.UserUnit);
                    return sortFilterCategory.EqualsSpecialAttack(specialAttack);
                })
                .Where(model => sortFilterCategory.EqualsAbility(model.MstUnit.MstUnitAbilityModels))
                .Where(model => sortFilterCategory.EqualsSeries(model.MstUnit.MstSeriesId))
                .Where(model => sortFilterCategory.EqualsBonus(model.PartyBonusUnit))
                .Where(model => sortFilterCategory.EqualsFormation(
                    model.IsAchievedSpecialRuleUnit,
                    model.IsNotAchieveSpecialRuleUnit))
                .ToList();
        }

        bool IUnitListFilterAndSort.HasAnyMatchingFilterUnit(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds)
        {
            var filterUnits = CreateFilterUnitModels(
                userUnits,
                mstUnits,
                partyBonusUnits,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds);

            return filterUnits
                .Any(model => sortFilterCategory.EqualsColor(model.MstUnit.Color) &&
                              sortFilterCategory.EqualsRole(model.MstUnit.RoleType) &&
                              sortFilterCategory.EqualsAttackRange(model.MstUnit.AttackRangeType) &&
                              sortFilterCategory.EqualsRarity(model.MstUnit.Rarity) &&
                              sortFilterCategory.EqualsSpecialAttack(
                                  SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                                      model.MstUnit, model.UserUnit)) &&
                              sortFilterCategory.EqualsAbility(model.MstUnit.MstUnitAbilityModels) &&
                              sortFilterCategory.EqualsSeries(model.MstUnit.MstSeriesId) &&
                              sortFilterCategory.EqualsBonus(model.PartyBonusUnit) &&
                              sortFilterCategory.EqualsFormation(
                                  model.IsAchievedSpecialRuleUnit,
                                  model.IsNotAchieveSpecialRuleUnit));
        }

        IReadOnlyList<UserUnitModel> IUnitListFilterAndSort.Sort(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            UnitListSortType sortType,
            UnitListSortOrder sortOrder)
        {
            var filterUnits = CreateFilterUnitModels(
                userUnits,
                mstUnits,
                partyBonusUnits,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds);

            return Sort(filterUnits, seriesModels, specialRuleUnitStatusModels, sortType, sortOrder);
        }

        IReadOnlyList<UserUnitModel> Sort(
            IReadOnlyList<FilterUnitModel> userUnits,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            UnitListSortType sortType,
            UnitListSortOrder sortOrder)
        {
            var seriesPrefixWordSortModels = SeriesPrefixWordSortHelper.PrefixWordSort(seriesModels);
            // 第一優先ソート
            var orderedUnits = SortList(userUnits, specialRuleUnitStatusModels, sortType, sortOrder);

            // 第二優先。レアリティ(降順)(元々レアリティソートをしていた場合はスキップ)
            // 第三優先。キャラID(昇順)
            IReadOnlyList<FilterUnitModel> sortedUnits;
            if (sortType == UnitListSortType.Rarity)
            {
                sortedUnits = orderedUnits
                    .ThenBy(model => model.MstUnit.Id)
                    .ToList();
            }
            else
            {
                sortedUnits = orderedUnits
                    .ThenByDescending(model => model.MstUnit.Rarity)
                    .ThenBy(model => model.MstUnit.Id)
                    .ToList();
            }

            // イベントボーナスの場合、イベントボーナスがないユニットを後ろに持っていく
            if (sortType == UnitListSortType.EventBonus)
            {
                var noEventBonusUnits = sortedUnits
                    .Where(model => model.PartyBonusUnit.IsEmpty())
                    .ToList();

                sortedUnits = sortedUnits
                    .Where(model => !model.PartyBonusUnit.IsEmpty())
                    .Concat(noEventBonusUnits)
                    .ToList();
            }

            var sortedList = sortedUnits
                .Select(model => model.UserUnit)
                .ToList();

            return sortedList;
        }

        IReadOnlyList<FilterUnitModel> CreateFilterUnitModels(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds)
        {
            return userUnits
                .Select(unit => CreateFilterUnitModel(
                    unit,
                    mstUnits,
                    partyBonusUnits,
                    achievedSpecialRuleUnitIds,
                    notAchieveSpecialRuleUnitIds))
                .ToList();
        }

        FilterUnitModel CreateFilterUnitModel(UserUnitModel userUnit,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds)
        {
            var mstUnit = mstUnits.First(mst => mst.Id == userUnit.MstUnitId);
            var partyBonusUnit =
                partyBonusUnits.FirstOrDefault(mst => mst.MstUnitId == userUnit.MstUnitId, PartyBonusUnitModel.Empty);
            var achievedSpecialRuleFlag =
                new FilterAchievedSpecialRuleFlag(achievedSpecialRuleUnitIds.Contains(userUnit.UsrUnitId));
            var notAchieveSpecialRuleFlag =
                new FilterNotAchieveSpecialRuleFlag(notAchieveSpecialRuleUnitIds.Contains(userUnit.UsrUnitId));
            return new FilterUnitModel(mstUnit, userUnit, partyBonusUnit, achievedSpecialRuleFlag, notAchieveSpecialRuleFlag);
        }

        IOrderedEnumerable<FilterUnitModel> SortList(
            IReadOnlyList<FilterUnitModel> userUnits,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            UnitListSortType sortType,
            UnitListSortOrder sortOrder)
        {
            var keySelector = GetKeySelector(sortType, specialRuleUnitStatusModels);
            if (sortOrder == UnitListSortOrder.Ascending)
            {
                return userUnits.OrderBy(keySelector);
            }
            else
            {
                return userUnits.OrderByDescending(keySelector);
            }
        }

        Func<FilterUnitModel, IComparable> GetKeySelector(
            UnitListSortType sortType,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            return sortType switch
            {
                UnitListSortType.Rarity => model => model.MstUnit.Rarity,
                UnitListSortType.Grade => model => model.UserUnit.Grade,
                UnitListSortType.Level => model => model.UserUnit.Level,
                UnitListSortType.Hp => model =>
                {
                    // スペシャルの場合
                    if (model.MstUnit.IsSpecialUnit) return HP.Zero;

                    var calculateStatus = UnitStatusCalculateHelper.Calculate(
                        model.MstUnit,
                        model.UserUnit.Level,
                        model.UserUnit.Rank, model.UserUnit.Grade);
                    var calculateStatusWithSpecialRule = UnitStatusCalculateHelper.CalculateStatusWithSpecialRule(
                        calculateStatus,
                        model.MstUnit.Id,
                        specialRuleUnitStatusModels);
                    return calculateStatusWithSpecialRule.HP;
                },
                UnitListSortType.Attack => model =>
                {
                    // スペシャルの場合
                    if (model.MstUnit.IsSpecialUnit) return AttackPower.Zero;

                    var calculateStatus = UnitStatusCalculateHelper.Calculate(
                        model.MstUnit,
                        model.UserUnit.Level,
                        model.UserUnit.Rank,
                        model.UserUnit.Grade);
                    var calculateStatusWithSpecialRule = UnitStatusCalculateHelper.CalculateStatusWithSpecialRule(
                        calculateStatus,
                        model.MstUnit.Id,
                        specialRuleUnitStatusModels);
                    return calculateStatusWithSpecialRule.AttackPower;
                },
                UnitListSortType.LeaderPoint => model => model.MstUnit.SummonCost,
                UnitListSortType.AttackRange => model => model.MstUnit.AttackRangeType,
                UnitListSortType.Speed => model =>
                {
                    // スペシャルの場合
                    if (model.MstUnit.IsSpecialUnit) return UnitMoveSpeedType.None;

                    return model.MstUnit.UnitMoveSpeed.ToConvertedSpeedType();
                },
                UnitListSortType.EventBonus => model => model.PartyBonusUnit.BonusPercentage,
                _ => throw new ArgumentOutOfRangeException(nameof(sortType), sortType, null)
            };
        }
    }
}
