using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases
{
    public class UpdateArtworkSortOrderUseCase
    {
        [Inject] IArtworkSortFilterCacheRepository ArtworkSortFilterCacheRepository { get; }

        public void UpdateSortOrder(ArtworkListSortOrder sortOrder, ArtworkSortFilterCacheType cacheType)
        {
            ArtworkSortFilterCacheRepository.UpdateSortOrder(sortOrder, cacheType);
        }
    }
}
