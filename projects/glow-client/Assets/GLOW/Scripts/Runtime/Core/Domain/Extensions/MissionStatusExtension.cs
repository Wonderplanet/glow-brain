using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Extensions
{
    public static class MissionStatusExtension
    {
        public static MissionClearFrag IsClearedStatus(this MissionStatus missionStatus)
        {
            return missionStatus switch
            {
                MissionStatus.Nothing => MissionClearFrag.False,
                MissionStatus.Receivable => MissionClearFrag.True,
                MissionStatus.Received => MissionClearFrag.True,
                _ => MissionClearFrag.False
            };
        }
        
        public static MissionReceivedFlag IsReceivedStatus(this MissionStatus missionStatus)
        {
            return missionStatus switch
            {
                MissionStatus.Nothing => MissionReceivedFlag.False,
                MissionStatus.Receivable => MissionReceivedFlag.False,
                MissionStatus.Received => MissionReceivedFlag.True,
                _ => MissionReceivedFlag.False
            };
        }
    }
}