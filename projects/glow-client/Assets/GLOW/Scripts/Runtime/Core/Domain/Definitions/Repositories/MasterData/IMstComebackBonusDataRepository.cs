using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstComebackBonusDataRepository
    {
        IReadOnlyList<MstComebackBonusModel> GetMstComebackBonusModels(MasterDataId mstComebackBonusScheduleId);
        MstComebackBonusScheduleModel GetMstComebackBonusScheduleModelFirstOrDefault(MasterDataId mstComebackBonusScheduleId);
        MstDailyBonusRewardModel GetMstDailyBonusRewardModelFirstOrDefault(MasterDataId mstDailyBonusRewardGroupId);
    }
}