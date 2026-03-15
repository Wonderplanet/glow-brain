using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MissionArtworkPanelRewardCountModel(
        MasterDataId MstArtworkPanelMissionId,
        UnreceivedMissionRewardCount UnreceivedMissionArtworkPanelRewardCount)
    {
        public static MissionArtworkPanelRewardCountModel Empty { get; } = new(
            MasterDataId.Empty,
            UnreceivedMissionRewardCount.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}