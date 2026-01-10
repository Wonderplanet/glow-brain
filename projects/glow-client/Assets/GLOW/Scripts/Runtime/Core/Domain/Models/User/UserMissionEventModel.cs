using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionEventModel(
        MasterDataId MstMissionEventId,
        MissionProgress Progress,
        MissionClearFrag IsCleared,
        MissionReceivedFlag IsReceivedReward)
    {
        public static UserMissionEventModel Empty { get; } = new(
            MasterDataId.Empty,
            MissionProgress.Empty, 
            MissionClearFrag.False, 
            MissionReceivedFlag.False);
        
        public static UserMissionEventModel EmptyWithId(MasterDataId id)
        {
            return Empty with
            {
                MstMissionEventId = id
            };
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
