using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstDailyBonusRewardDataTranslator
    {
        public static MstDailyBonusRewardModel ToMstDailyBonusRewardModel(MstDailyBonusRewardData data)
        {
            return new MstDailyBonusRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.GroupId),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount),
                new SortOrder(data.SortOrder));
        }
    }
}