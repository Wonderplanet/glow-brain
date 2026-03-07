using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases
{
    public class HasAnyMatchingFilterArtworkUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkEffectRepository MstArtworkEffectRepository { get; }

        public bool HasAnyMatchingFilterUnit(
            IReadOnlyList<MasterDataId> seriesIds,
            IReadOnlyList<ArtworkEffectType> artworkEffectTypes,
            ArtworkListSortType sortType,
            ArtworkListSortOrder sortOrder)
        {
            var filterCategoryModel = new ArtworkSortFilterCategoryModel(
                new FilterSeriesModel(seriesIds),
                new FilterArtworkEffectModel(artworkEffectTypes),
                sortType,
                sortOrder);

            if (!filterCategoryModel.IsAnyFilter())
            {
                return true;
            }

            var artworks = MstArtworkDataRepository.GetArtworks();
            var artworkEffects = artworks
                .Select(artwork => MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(artwork.Id))
                .ToList();

            return artworks.Zip(artworkEffects, (artwork, artworkEffect) => (artwork, artworkEffect))
                .Any(tuple => IsMatchFilterCategoryModel(tuple.artwork, tuple.artworkEffect, filterCategoryModel));
        }

        bool IsMatchFilterCategoryModel(
            MstArtworkModel mstArtwork,
            MstInGameArtworkEffectModel mstArtworkEffect,
            ArtworkSortFilterCategoryModel filterCategoryModel)
        {
            return filterCategoryModel.EqualsSeries(mstArtwork.MstSeriesId)
                   && mstArtworkEffect.MstArtworkEffectModels
                       .Select(effect => effect.Type)
                       .Any(filterCategoryModel.EqualsArtworkEffectType);
        }
    }
}
