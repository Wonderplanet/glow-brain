using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.UnitList.Domain.Misc
{
    public class ArtworkListFilterAndSort : IArtworkListFilterAndSort
    {
        record FilterArtworkModel(
            MstArtworkModel MstArtwork,
            UserArtworkModel UserArtwork,
            MstInGameArtworkEffectModel MstInGameArtworkEffect);

        IReadOnlyList<MstArtworkModel> IArtworkListFilterAndSort.FilterAndSort(
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<MstArtworkModel> mstArtworks,
            IReadOnlyList<MstInGameArtworkEffectModel> mstArtworkEffectModels,
            ArtworkSortFilterCategoryModel sortFilterCategory,
            IReadOnlyList<MstSeriesModel> seriesModels)
        {
            var filterArtworkModels = mstArtworks.Select(
                mstArtwork =>
                {
                    var userArtwork = userArtworks.FirstOrDefault(ua => ua.MstArtworkId == mstArtwork.Id, UserArtworkModel.Empty);
                    var mstArtworkEffect = mstArtworkEffectModels
                        .FirstOrDefault(ae => ae.MstArtworkId == mstArtwork.Id, MstInGameArtworkEffectModel.Empty);
                    return new FilterArtworkModel(mstArtwork, userArtwork, mstArtworkEffect);
                }).ToList();

            var filteredArtworkModels = Filter(filterArtworkModels, sortFilterCategory);
            return Sort(filteredArtworkModels, sortFilterCategory.SortType, sortFilterCategory.SortOrder);
        }

        IReadOnlyList<FilterArtworkModel> Filter(
            IReadOnlyList<FilterArtworkModel> filterArtworkModels,
            ArtworkSortFilterCategoryModel sortFilterCategory)
        {
            var models = filterArtworkModels
                .Where(model => sortFilterCategory.EqualsSeries(model.MstArtwork.MstSeriesId))
                .Where(model =>
                {
                    return model.MstInGameArtworkEffect.MstArtworkEffectModels
                        .Select(effect => effect.Type)
                        .Any(sortFilterCategory.EqualsArtworkEffectType);
                })
                .ToList();

            return models;
        }

        IReadOnlyList<MstArtworkModel> Sort(
            IReadOnlyList<FilterArtworkModel> filterArtworkModels,
            ArtworkListSortType sortType,
            ArtworkListSortOrder sortOrder)
        {
            // 第一優先。UserArtwork.IsEmpty(falseが先)
            var orderedByIsEmpty = filterArtworkModels.OrderBy(model => model.UserArtwork.IsEmpty());

            // 第二優先ソート
            var orderedArtworks = SortList(orderedByIsEmpty, sortType, sortOrder);

            // 第三優先。レアリティ(降順)(元々レアリティソートをしていた場合はスキップ)
            // 第四優先。アートワークID(昇順)
            IReadOnlyList<FilterArtworkModel> sortedArtworks;
            if (sortType == ArtworkListSortType.Rarity)
            {
                sortedArtworks = orderedArtworks
                    .ThenBy(model => model.MstArtwork.Id)
                    .ToList();
            }
            else
            {
                sortedArtworks = orderedArtworks
                    .ThenByDescending(model => model.MstArtwork.Rarity)
                    .ThenBy(model => model.MstArtwork.Id)
                    .ToList();
            }

            var sortedMstArtworks = sortedArtworks.Select(model => model.MstArtwork).ToList();

            return sortedMstArtworks;
        }

        IOrderedEnumerable<FilterArtworkModel> SortList(
            IOrderedEnumerable<FilterArtworkModel> filterArtworkModels,
            ArtworkListSortType sortType,
            ArtworkListSortOrder sortOrder)
        {
            var keySelector = GetKeySelector(sortType);
            if (sortOrder == ArtworkListSortOrder.Ascending)
            {
                return filterArtworkModels.ThenBy(keySelector);
            }
            else
            {
                return filterArtworkModels.ThenByDescending(keySelector);
            }
        }

        Func<FilterArtworkModel, IComparable> GetKeySelector(
            ArtworkListSortType sortType)
        {
            return sortType switch
            {
                ArtworkListSortType.Rarity => model => model.MstArtwork.Rarity,
                ArtworkListSortType.Grade => model => model.UserArtwork.Grade,
                _ => throw new ArgumentOutOfRangeException(nameof(sortType), sortType, null)
            };
        }
    }
}
