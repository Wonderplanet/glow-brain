using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstDefenseTargetDataRepository
    {
        MstDefenseTargetModel GetMstDefenseTargetModel(MasterDataId mstDefenseTargetId);
        IReadOnlyList<MstDefenseTargetModel> GetMstDefenseTargetModels();
    }
}
