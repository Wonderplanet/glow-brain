using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstMissionDailyDataRepository
    {
        public IReadOnlyList<MstMissionDailyModel> GetMstMissionDailyModels();
        public IReadOnlyList<MstMissionDailyBonusModel> GetMstMissionDailyBonusModels();
    }
}