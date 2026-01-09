using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class UserMissionEventDataTranslator
    {
        public static UserMissionEventModel ToUserMissionEventModel(UsrMissionEventData usrEventMissionData)
        {
            return new UserMissionEventModel(
                new MasterDataId(usrEventMissionData.MstMissionEventId),
                new MissionProgress(usrEventMissionData.Progress),
                new MissionClearFrag(usrEventMissionData.IsCleared),
                new MissionReceivedFlag(usrEventMissionData.IsReceivedReward));
        }
    }
}