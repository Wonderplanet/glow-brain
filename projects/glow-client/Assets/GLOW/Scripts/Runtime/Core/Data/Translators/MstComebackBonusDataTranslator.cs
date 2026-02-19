using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class MstComebackBonusDataTranslator
    {
        public static MstComebackBonusModel ToMstComebackBonusModel(MstComebackBonusData data)
        {
            return new MstComebackBonusModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstComebackBonusScheduleId),
                new LoginDayCount(data.LoginDayCount),
                new MasterDataId(data.MstDailyBonusRewardGroupId),
                new SortOrder(data.SortOrder));
        }
    }
}