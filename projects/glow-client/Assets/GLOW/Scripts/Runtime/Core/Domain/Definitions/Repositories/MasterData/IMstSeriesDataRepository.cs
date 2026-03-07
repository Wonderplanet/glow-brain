using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstSeriesDataRepository
    {
        IReadOnlyList<MstSeriesModel> GetMstSeriesModels();
        MstSeriesModel GetMstSeriesModel(MasterDataId id);
    }
}
