using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Translators
{
    public static class MissionStatusTranslator
    {
        public static MissionStatus ToMissionStatus(
            MissionClearFrag isCleared, 
            MissionReceivedFlag isReceivedReward, 
            bool isAllReceived = false)
        {
            if (!isCleared)
                return isAllReceived ? MissionStatus.AllReceived : MissionStatus.Nothing;
            else if(!isReceivedReward)
                return isAllReceived ? MissionStatus.AllReceived : MissionStatus.Receivable;
            else
                return MissionStatus.Received;
        }
    }
}