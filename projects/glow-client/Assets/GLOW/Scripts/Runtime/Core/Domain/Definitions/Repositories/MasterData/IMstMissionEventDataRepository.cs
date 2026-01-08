using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstMissionEventDataRepository
    {
        public IReadOnlyList<MstMissionEventModel> GetMstMissionEventModels();
        public IReadOnlyList<MstMissionEventDependencyModel> GetMstMissionEventDependencyModels();
        public IReadOnlyList<MstMissionEventDailyBonusModel> GetMstMissionEventDailyBonusModels(MasterDataId mstEventDailyBonusScheduleId);
        public MstMissionEventDailyBonusScheduleModel GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(MasterDataId mstEventId);
    }
}