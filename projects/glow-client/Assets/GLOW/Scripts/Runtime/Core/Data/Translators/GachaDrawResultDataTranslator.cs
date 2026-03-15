using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class GachaDrawResultDataTranslator
    {
        public static GachaDrawResultModel ToGachaDrawResultModel(GachaDrawResultData data)
        {
            var gachaResultModels = data.GachaResults != null
                ? new List<GachaResultModel>(data.GachaResults.Select(ToGachaResultModel))
                : new List<GachaResultModel>();

            var stepRewardModels = data.StepRewards != null
                ? new List<GachaResultModel>(data.StepRewards.Select(ToGachaResultModel))
                : new List<GachaResultModel>();

            var userUnitModels = data.UsrUnits != null
                ? new List<UserUnitModel>(data.UsrUnits.Select(UserUnitDataTranslator.ToUserUnitModel))
                : new List<UserUnitModel>();

            var userItemModels = data.UsrItems != null
                ? new List<UserItemModel>(data.UsrItems.Select(ItemDataTranslator.ToUserItemModel))
                : new List<UserItemModel>();

            var userGachaUpperModels = data.UsrGachaUppers != null
                ? new List<UserDrawCountThresholdModel>(data.UsrGachaUppers.Select(UserGachaUpperDataTranslator.ToUserDrawCountThresholdModel))
                : new List<UserDrawCountThresholdModel>();

            GachaDrawResultModel gachaDrawResultModel = new GachaDrawResultModel(
                gachaResultModels,
                stepRewardModels,
                userUnitModels,
                userItemModels,
                UserParameterTranslator.ToUserParameterModel(data.UsrParameter),
                userGachaUpperModels,
                UserGachaDataTranslator.ToUserGachaModel(data.UsrGacha)
            );
            return gachaDrawResultModel;
        }

        static GachaResultModel ToGachaResultModel(GachaResultData data)
        {
            return new GachaResultModel(
                RewardDataTranslator.Translate(data.Reward), 
                data.PrizeType);
        }
    }
}
