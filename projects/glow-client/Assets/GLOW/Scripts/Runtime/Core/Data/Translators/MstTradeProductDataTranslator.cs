using System.Collections.Generic;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Core.Data.Translators
{
    public class MstTradeProductDataTranslator
    {
        public static MstTradeProductModel Translate(
            MstExchangeData mstExchangeData,
            IReadOnlyList<MstExchangeLineupData> mstExchangeLineupDatas,
            IReadOnlyList<MstExchangeRewardData> mstExchangeRewardDatas,
            IReadOnlyList<MstExchangeCostData> mstExchangeCostDatas)
        {
            List<MstExchangeLineupModel> lineupModel = new List<MstExchangeLineupModel>();

            for(int i = 0; i < mstExchangeLineupDatas.Count; i++)
            {
                var lineupData = mstExchangeLineupDatas[i];
                var rewardData = mstExchangeRewardDatas[i];
                var costData = mstExchangeCostDatas[i];

                if(rewardData == null || costData == null)
                {
                    continue;
                }

                lineupModel.Add(TranslateLineupModel(
                    lineupData,
                    rewardData,
                    costData,
                    mstExchangeData.EndAt.HasValue
                        ? new ExchangeShopEndTime(mstExchangeData.EndAt.Value)
                        : ExchangeShopEndTime.Unlimited
                ));
            }

            return new MstTradeProductModel(
                new MasterDataId(mstExchangeData.Id),
                new MasterDataId(mstExchangeData.LineupGroupId),
                new ExchangeShopStartTime(mstExchangeData.StartAt),
                mstExchangeData.EndAt.HasValue
                    ? new ExchangeShopEndTime(mstExchangeData.EndAt.Value)
                    : ExchangeShopEndTime.Unlimited,
                lineupModel
                );
        }

        static MstExchangeLineupModel TranslateLineupModel(
            MstExchangeLineupData lineupData,
            MstExchangeRewardData rewardData,
            MstExchangeCostData costData,
            ExchangeShopEndTime endTime)
        {
            return new MstExchangeLineupModel(
                new MasterDataId(lineupData.Id),
                lineupData.TradableCount.HasValue ?
                    new PurchasableCount(lineupData.TradableCount.Value) : PurchasableCount.Infinity,
                new MasterDataId(rewardData.ResourceId),
                rewardData.ResourceType,
                new ProductResourceAmount(rewardData.ResourceAmount),
                new MasterDataId(costData.CostId),
                costData.CostType,
                new ItemAmount(costData.CostAmount),
                endTime,
                new SortOrder(lineupData.DisplayOrder)
            );
        }
    }
}
