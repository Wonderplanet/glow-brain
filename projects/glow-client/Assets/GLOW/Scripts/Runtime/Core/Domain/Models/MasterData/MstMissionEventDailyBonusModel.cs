using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionEventDailyBonusModel(
        MasterDataId Id,
        MasterDataId MstMissionEventDailyBonusScheduleId,
        LoginDayCount LoginDayCount,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder);
}