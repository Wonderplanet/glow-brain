using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators.StaminaRecover
{
    public static class MissionEventRewardCountDataTranslator
    {
        public static MissionEventRewardCountModel ToModel(MissionEventRewardCountData data)
        {
            return new MissionEventRewardCountModel(
                new MasterDataId(data.MstEventId),
                new UnreceivedMissionRewardCount(data.Count)
            );
        }
    }
}