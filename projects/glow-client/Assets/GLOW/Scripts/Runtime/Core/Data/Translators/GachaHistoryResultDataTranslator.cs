using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class GachaHistoryResultDataTranslator
    {
        public static GachaHistoryResultModel ToGachaDrawResultModel(GachaHistoryResultData data)
        {
            return new GachaHistoryResultModel(data.GachaHistories.Select(ToGachaHistoryModel).ToList());
        }
        
        static GachaHistoryModel ToGachaHistoryModel(GachaHistoryData data)
        {
            return new GachaHistoryModel(
                new MasterDataId(data.OprGachaId),
                data.CostType,
                new MasterDataId(data.CostId),
                new CostAmount(data.CostNum),
                data.PlayedAt,
                data.Results.Select(ToGachaHistoryRewardModel).ToList());
        }
        
        static GachaHistoryRewardModel ToGachaHistoryRewardModel(GachaHistoryRewardData data)
        {
            return new GachaHistoryRewardModel(
                new SortOrder(data.SortOrder),
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}