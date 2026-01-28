using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstStageRewardDataTranslator
    {
        public static MstStageRewardModel ToMstStageRewardModel(MstStageRewardData data)
        {
            return new MstStageRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstStageId),
                data.RewardCategory,
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount),
                new StageRewardPercentage(data.Percentage),
                new StageRewardSortOrder(data.SortOrder));
        }
    }
}
