using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstEmblemRepository
    {
        MstEmblemModel GetMstEmblemFirstOrDefault(MasterDataId mstEmblemId);
        IReadOnlyList<MstEmblemModel> GetSeriesEmblems(MasterDataId mstSeriesId);
        IReadOnlyList<MstEmblemModel> GetMstEmblems();
    }
}
