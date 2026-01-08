using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionBeginnerModel(
        MasterDataId MstMissionBeginnerId, 
        MissionProgress Progress, 
        MissionClearFrag IsCleared,
        MissionReceivedFlag IsReceivedReward)
    {
        public static UserMissionBeginnerModel Empty { get; } = new(
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
