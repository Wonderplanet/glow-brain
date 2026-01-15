using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstStageEnhanceRewardParamDataRepository
    {
        IReadOnlyList<MstStageEnhanceRewardParamModel> GetStageEnhanceRewardParams();
    }
}
