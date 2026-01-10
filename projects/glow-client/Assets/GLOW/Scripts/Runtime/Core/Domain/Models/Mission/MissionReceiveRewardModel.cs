using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionReceiveRewardModel(
        MissionType MissionType,
        MasterDataId MstMissionId,
        UnreceivedRewardReasonType UnreceivedRewardReason)
    {
        public static MissionReceiveRewardModel Empty { get; } =  new MissionReceiveRewardModel(
            MissionType.Achievement,
            MasterDataId.Empty,
            UnreceivedRewardReasonType.None);
    }
}
