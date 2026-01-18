using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class MissionStatusDataTranslator
    {
        public static MissionStatusModel ToMissionStatusModel(MissionStatusData missionStatusData)
        {
            return new MissionStatusModel(
                new MissionAllCompleted(missionStatusData.IsBeginnerMissionCompleted)
            );
        }
    }
}