using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MissionRewardDataTranslator
    {
        public static MissionRewardModel ToMissionRewardModel(MissionRewardData data)
        {
            return new MissionRewardModel(
                data.MissionType,
                new MasterDataId(data.MstMissionId),
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}
