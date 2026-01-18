using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Item;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class ItemExchangeSelectItemResultDataTranslator
    {
        public static ItemExchangeSelectItemResultModel ToItemExchangeSelectItemResultModel(ItemExchangeSelectItemResultData data)
        {
            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToArray();

            var rewardModels = data.ItemRewards
                .Select(ToPlayerResourceResultModel)
                .ToList();

            return new ItemExchangeSelectItemResultModel(
                userItemModels,
                rewardModels);
        }

        static RewardModel ToPlayerResourceResultModel(ItemRewardData data)
        {
            return RewardDataTranslator.Translate(data.Reward);
        }
    }
}
