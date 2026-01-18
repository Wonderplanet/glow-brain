using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class UserMissionLimitedTermDataTranslator
    {
        public static UserMissionLimitedTermModel ToUserMissionLimitedTermModel(UsrMissionLimitedTermData usrMissionLimitedTermData)
        {
            return new UserMissionLimitedTermModel(
                new MasterDataId(usrMissionLimitedTermData.MstMissionLimitedTermId),
                new MissionProgress(usrMissionLimitedTermData.Progress),
                new MissionClearFrag(usrMissionLimitedTermData.IsCleared),
                new MissionReceivedFlag(usrMissionLimitedTermData.IsReceivedReward));
        }
    }
}