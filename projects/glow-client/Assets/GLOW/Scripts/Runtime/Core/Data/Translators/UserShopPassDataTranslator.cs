using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Data.Translators
{
    public class UserShopPassDataTranslator
    {
        public static UserShopPassModel ToUserShopPassModel(UsrShopPassData data)
        {
            return new UserShopPassModel(
                new MasterDataId(data.MstShopPassId),
                new DailyRewardReceivedCount(data.DailyRewardReceivedCount),
                new DailyLatestReceivedPassAt(data.DailyLatestReceivedAt),
                new PassStartAt(data.StartAt),
                new PassEndAt(data.EndAt));
        }
    }
}