using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MissionEventRewardCountModel(
        MasterDataId MstEventId,
        UnreceivedMissionRewardCount UnreceivedMissionEventRewardCount)
    {
        public static MissionEventRewardCountModel Empty { get; } = new(
            MasterDataId.Empty,
            UnreceivedMissionRewardCount.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}