using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public interface IUnitListFilterAndSort
    {
        IReadOnlyList<UserUnitModel> FilterAndSort(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        IReadOnlyList<UserUnitModel> Filter(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds);

        bool HasAnyMatchingFilterUnit(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            UnitSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<MstUnitLevelUpModel> unitLevelUpModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds);

        IReadOnlyList<UserUnitModel> Sort(
            IReadOnlyList<UserUnitModel> userUnits,
            IReadOnlyList<MstCharacterModel> mstUnits,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            IReadOnlyList<MstSeriesModel> seriesModels,
            IReadOnlyList<UserDataId> achievedSpecialRuleUnitIds,
            IReadOnlyList<UserDataId> notAchieveSpecialRuleUnitIds,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            UnitListSortType sortType,
            UnitListSortOrder sortOrder);
    }
}
