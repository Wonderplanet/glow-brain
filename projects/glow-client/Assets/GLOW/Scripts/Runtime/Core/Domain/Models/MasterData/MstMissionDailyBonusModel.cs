using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionDailyBonusModel(
        MasterDataId Id,
        MissionDailyBonusType MissionDailyBonusType,
        LoginDayCount LoginDayCount,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder);
}