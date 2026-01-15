using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionDailyModel(
        MasterDataId MstMissionDailyId,
        MissionProgress Progress,
        MissionClearFrag IsCleared,
        MissionReceivedFlag IsReceivedReward)
    {
        public static UserMissionDailyModel Empty { get; } = new(
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
