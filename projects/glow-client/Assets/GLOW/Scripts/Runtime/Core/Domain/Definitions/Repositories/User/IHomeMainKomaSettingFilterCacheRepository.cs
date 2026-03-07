using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IHomeMainKomaSettingFilterCacheRepository
    {
        IReadOnlyList<MasterDataId> CachedFilterMstSeriesIds { get; }
        void UpdateFilterMstSeriesIds(IReadOnlyList<MasterDataId> filterMstSeriesIds);
    }
}