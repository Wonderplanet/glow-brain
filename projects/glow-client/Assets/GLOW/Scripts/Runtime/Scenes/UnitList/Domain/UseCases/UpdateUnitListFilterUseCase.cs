using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.UnitList.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.UnitList.Domain.UseCases
{
    public class UpdateUnitListFilterUseCase
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }

        public void UpdateSortOrder(UnitListSortOrder sortOrder, UnitSortFilterCacheType cacheType)
        {
            UnitSortFilterCacheRepository.UpdateSortOrder(sortOrder, cacheType);
        }
    }
}
