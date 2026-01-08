using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Translators
{
    public static class UserTradePackDataTranslator
    {
        public static UserTradePackModel Translate(UsrTradePackData data)
        {
            return new UserTradePackModel(
                new MasterDataId(data.MstPackId),
                new PurchaseCount(data.DailyTradeCount));
        }
    }
}
