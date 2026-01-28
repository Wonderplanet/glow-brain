using System.Collections.Generic;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprGachaUseResourceRepository
    {
        IReadOnlyList<OprGachaUseResourceModel> FindByGachaId(MasterDataId gachaId);
        IReadOnlyList<OprGachaUseResourceModel> GetOprGachaUseResourceModelsByItemId(MasterDataId mstCostId);
    }
}
