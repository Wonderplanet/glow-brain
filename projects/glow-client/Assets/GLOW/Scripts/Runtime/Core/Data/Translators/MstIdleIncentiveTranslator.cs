using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;

namespace GLOW.Core.Data.Translators
{
    public static class MstIdleIncentiveTranslator
    {
        public static MstIdleIncentiveModel ToMstIdleIncentiveModel(MstIdleIncentiveData data)
        {
            return new MstIdleIncentiveModel(
                new MasterDataId(data.Id),
                TimeSpan.FromMinutes(data.InitialRewardReceiveMinutes),
                TimeSpan.FromHours(data.MaxIdleHours),
                new IdleIncentiveReceiveCount(data.MaxDailyDiamondQuickReceiveAmount),
                new ObscuredPlayerResourceAmount(data.RequiredQuickReceiveDiamondAmount),
                new IdleIncentiveReceiveCount(data.MaxDailyAdQuickReceiveAmount),
                TimeSpan.FromSeconds(data.AdIntervalSeconds),
                TimeSpan.FromMinutes(data.QuickIdleMinutes),
                TimeSpan.FromMinutes(data.RewardIncreaseIntervalMinutes)
                );
        }

        public static MstIdleIncentiveItemModel ToMstIdleIncentiveItemModel(MstIdleIncentiveItemData data)
        {
            return new MstIdleIncentiveItemModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstIdleIncentiveItemGroupId),
                new MasterDataId(data.MstItemId),
                new IdleIncentiveRewardAmount(data.BaseAmount)
            );
        }

        public static MstIdleIncentiveRewardModel ToMstIdleIncentiveRewardModel(MstIdleIncentiveRewardData data)
        {
            return new MstIdleIncentiveRewardModel(
                string.IsNullOrEmpty(data.MstStageId) ? MasterDataId.Empty : new MasterDataId(data.MstStageId),
                new IdleIncentiveRewardAmount(data.BaseCoinAmount),
                new IdleIncentiveRewardAmount(data.BaseExpAmount),
                new MasterDataId(data.MstIdleIncentiveItemGroupId)
            );
        }
    }
}
