using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UsrExchangeLineupDataTranslator
    {
        public static UserExchangeLineupModel Translate(UsrExchangeLineupData data)
        {
            return new UserExchangeLineupModel(
                new MasterDataId(data.MstExchangeId),
                new MasterDataId(data.MstExchangeLineupId),
                new ItemAmount(data.TradeCount));
        }
    }
}
