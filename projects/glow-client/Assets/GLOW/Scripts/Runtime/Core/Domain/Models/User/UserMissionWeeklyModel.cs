using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionWeeklyModel(
        MasterDataId MstMissionWeeklyId,
        MissionProgress Progress,
        MissionClearFrag IsCleared,
        MissionReceivedFlag IsReceivedReward)
    {
        public static UserMissionWeeklyModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionProgress.Empty, 
            MissionClearFrag.False, 
            MissionReceivedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        } 
    }
}
