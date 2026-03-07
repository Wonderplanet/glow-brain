using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitList.Domain.UseCases
{
    public class GetUnitListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IUnitEnhanceNotificationHelper UnitEnhanceNotificationHelper { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] ISeriesPrefixWordSortHelper SeriesPrefixWordSortHelper { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitListFilterAndSort UnitListFilterAndSort { get; }
        [Inject] IMstEventBonusUnitDataRepository MstEventBonusUnitDataRepository { get; }

        public UnitListModel GetUnitList()
        {
            // ソートとフィルターを適用
            var filterCategoryModel = UnitSortFilterCacheRepository.GetModel(UnitSortFilterCacheType.UnitList);
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var mstUnits = userUnits
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId))
                .ToList();

            var partyBonusUnits = PartyCacheRepository.GetBonusUnits();

            var mstSeriesModels = MstSeriesDataRepository.GetMstSeriesModels();

            var userUnitIds = userUnits.Select(unit => unit.UsrUnitId).ToList();

            var mstUnitLevelUpModels = MstUnitLevelUpRepository.GetUnitLevelUpList();

            var filteredUnits = UnitListFilterAndSort.FilterAndSort(
                userUnits,
                mstUnits,
                partyBonusUnits,
                filterCategoryModel,
                mstSeriesModels,
                mstUnitLevelUpModels,
                userUnitIds, // キャラ一覧画面では特別ルールのフィルター使わないためuserUnitIdsを渡している
                userUnitIds, // キャラ一覧画面では特別ルールのフィルター使わないためuserUnitIdsを渡している
                new List<MstInGameSpecialRuleUnitStatusModel>()); // キャラ一覧画面では特別ルールのステータス上昇は使わないため空リストを渡している

            var cellModels = filteredUnits
                .Select(unit => TranslateUnitListCellModel(unit, filterCategoryModel.SortType))
                .ToList();

            return new UnitListModel(
                cellModels,
                filterCategoryModel);
        }

        UnitListCellModel TranslateUnitListCellModel(UserUnitModel userUnit, UnitListSortType sortType)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstCharacter, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var characterIcon = CharacterIconModelFactory.CreateLSize(mstCharacter, userUnit, calculateStatus);
            var badge = UnitEnhanceNotificationHelper.GetUnitNotification(userUnit);
            return new UnitListCellModel(userUnit.UsrUnitId, characterIcon, badge, sortType);
        }
    }
}
