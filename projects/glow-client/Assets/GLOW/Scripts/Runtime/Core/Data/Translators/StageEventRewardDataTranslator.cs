using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class StageEventRewardDataTranslator
    {
        public static MstStageEventRewardModel CreateMstStageEventRewardModel(MstStageEventRewardData data)
        {
            return new MstStageEventRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstStageId),
                data.RewardCategory,
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new Percentage(data.Percentage),
                new ObscuredPlayerResourceAmount(data.ResourceAmount),
                new SortOrder(data.SortOrder)
            );
        }
    }
}
