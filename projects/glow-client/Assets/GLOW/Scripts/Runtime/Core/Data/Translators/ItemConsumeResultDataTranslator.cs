using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Item;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public static class ItemConsumeResultDataTranslator
    {
        public static ItemConsumeResultModel ToItemConsumeResultModel(ItemConsumeResultData data)
        {
            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToArray();

            var parameterData = data.UsrParameter;
            var userParameterModel = new UserParameterModel(
                new UserLevel(parameterData.Level),
                new UserExp(parameterData.Exp),
                new Coin(parameterData.Coin),
                new Stamina(parameterData.Stamina),
                parameterData.StaminaUpdatedAt,
                new FreeDiamond(parameterData.FreeDiamond),
                new PaidDiamondIos(parameterData.PaidDiamondIos),
                new PaidDiamondAndroid(parameterData.PaidDiamondAndroid),
                new UserDailyBuyStamina(parameterData.DailyBuyStaminaDiamondLimit, parameterData.DailyBuyStaminaAdLimit));

            var rewardModels = data.ItemRewards
                .Select(ToPlayerResourceResultModel)
                .ToList();

            var userItemTradeModel = UserItemTradeModelTranslator.ToUserItemTradeModel(data.UsrItemTrade);

            return new ItemConsumeResultModel(
                userItemModels,
                userParameterModel,
                rewardModels,
                userItemTradeModel);
        }

        static RewardModel ToPlayerResourceResultModel(ItemRewardData data)
        {
            return RewardDataTranslator.Translate(data.Reward);
        }
    }
}
