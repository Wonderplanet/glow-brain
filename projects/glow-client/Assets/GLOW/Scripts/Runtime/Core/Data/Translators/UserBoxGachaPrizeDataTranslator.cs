using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public static class UserBoxGachaPrizeDataTranslator
    {
        public static UserBoxGachaPrizeModel ToUserBoxGachaPrizeModel(UsrBoxGachaPrizeData data)
        {
            var mstBoxGachaPrizeId = new MasterDataId(data.MstBoxGachaPrizeId);
            var drawCount = new GachaDrawCount(data.Count);

            return new UserBoxGachaPrizeModel(mstBoxGachaPrizeId, drawCount);
        }
    }
}