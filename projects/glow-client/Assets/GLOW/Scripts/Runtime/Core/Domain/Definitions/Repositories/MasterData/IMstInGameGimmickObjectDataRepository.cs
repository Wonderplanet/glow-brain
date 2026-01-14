using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstInGameGimmickObjectDataRepository
    {
        MstInGameGimmickObjectModel GetMstInGameGimmickObjectModel(MasterDataId id);
        IReadOnlyList<MstInGameGimmickObjectModel> GetMstInGameGimmickObjectModels();
    }
}
