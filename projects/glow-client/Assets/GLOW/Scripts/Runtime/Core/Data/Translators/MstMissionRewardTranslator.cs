using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstMissionRewardTranslator
    {
        public static MstMissionRewardModel ToMMissionRewardModel(MstMissionRewardData rewardData)
        {
            return new MstMissionRewardModel(
                new MasterDataId(rewardData.Id),
                new MasterDataId(rewardData.GroupId),
                rewardData.ResourceType,
                new MasterDataId(rewardData.ResourceId),
                new ObscuredPlayerResourceAmount(rewardData.ResourceAmount),
                new SortOrder(rewardData.SortOrder)
            );
        }
    }
}