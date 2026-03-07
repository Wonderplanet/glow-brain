using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases
{
    public class GetArtworkSortAndFilterUseCase
    {
        [Inject] IArtworkSortFilterCacheRepository ArtworkSortFilterCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }

        public ArtworkSortAndFilterDialogUseCaseModel GetArtworkSortAndFilterDialogModel
            (ArtworkSortFilterCacheType cacheType)
        {
            var artworkSortFilterCategoryModel = ArtworkSortFilterCacheRepository.GetModel(cacheType);
            var mstSeriesModels = MstSeriesDataRepository.GetMstSeriesModels();

            return new ArtworkSortAndFilterDialogUseCaseModel(
                cacheType,
                artworkSortFilterCategoryModel,
                mstSeriesModels);
        }
    }
}
