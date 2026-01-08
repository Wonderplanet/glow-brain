using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class MstMissionEventDailyBonusTranslator
    {
        public static MstMissionEventDailyBonusModel ToMstMissionEventDailyBonusModel(MstMissionEventDailyBonusData data)
        {
            return new MstMissionEventDailyBonusModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstMissionEventDailyBonusScheduleId),
                new LoginDayCount(data.LoginDayCount),
                new MasterDataId(data.MstMissionRewardGroupId),
                new SortOrder(data.SortOrder));
        }
        
        public static MstMissionEventDailyBonusScheduleModel ToMstMissionEventDailyBonusScheduleModel(
            MstMissionEventDailyBonusScheduleData data)
        {
            return new MstMissionEventDailyBonusScheduleModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstEventId),
                data.StartAt,
                data.EndAt);
        }
    }
}