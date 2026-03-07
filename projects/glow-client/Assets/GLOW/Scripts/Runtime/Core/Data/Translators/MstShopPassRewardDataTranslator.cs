using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstShopPassRewardDataTranslator
    {
        public static MstShopPassRewardModel ToMstShopPassRewardModel(MstShopPassRewardData data)
        {
            return new MstShopPassRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstShopPassId),
                data.PassRewardType,
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount));
        }
    }
}