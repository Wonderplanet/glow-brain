using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstMissionLimitedDataRepository
    {
        public IReadOnlyList<MstMissionLimitedTermModel> GetMstMissionLimitedTermModels();
        public IReadOnlyList<MstMissionLimitedTermDependencyModel> GetMstMissionLimitedTermDependencyModels();
    }
}