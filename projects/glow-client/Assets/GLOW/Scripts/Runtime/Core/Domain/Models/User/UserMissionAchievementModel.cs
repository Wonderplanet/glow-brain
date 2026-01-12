using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionAchievementModel(
        MasterDataId MstMissionAchievementId,
        MissionProgress Progress,
        MissionClearFrag IsCleared,
        MissionReceivedFlag IsReceivedReward)
    {
        public static UserMissionAchievementModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionProgress.Empty,
            MissionClearFrag.False,
            MissionReceivedFlag.False);
        
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
