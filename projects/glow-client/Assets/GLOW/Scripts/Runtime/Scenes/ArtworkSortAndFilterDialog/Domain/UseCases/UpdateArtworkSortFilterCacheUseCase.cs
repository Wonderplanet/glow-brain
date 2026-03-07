using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases
{
    public class UpdateArtworkSortFilterCacheUseCase
    {
        [Inject] IArtworkSortFilterCacheRepository ArtworkSortFilterCacheRepository { get; }

        public void UpdateArtworkSortFilterCache(
            ArtworkSortFilterCacheType cacheType,
            IReadOnlyList<MasterDataId> seriesIds,
            IReadOnlyList<ArtworkEffectType> effectTypes,
            ArtworkListSortType sortType,
            ArtworkListSortOrder sortOrder)
        {
            var updateModel = new ArtworkSortFilterCategoryModel(
                new FilterSeriesModel(seriesIds),
                new FilterArtworkEffectModel(effectTypes),
                sortType,
                sortOrder);

            ArtworkSortFilterCacheRepository.UpdateModel(updateModel, cacheType);
        }
    }
}
