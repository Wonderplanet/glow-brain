using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.UseCase
{
    public class SavePackProductDisplayedFlagUseCase
    {
        [Inject] IShopProductCacheRepository ProductCacheRepository { get; }

        public void SaveDisplayFlag(List<MasterDataId> oprProductIds)
        {
            var displayedIds = ProductCacheRepository.DisplayedOprPackProductIds;
            displayedIds.AddRange(oprProductIds);
            ProductCacheRepository.DisplayedOprPackProductIds = displayedIds.Distinct().ToList();
        }
    }
}
