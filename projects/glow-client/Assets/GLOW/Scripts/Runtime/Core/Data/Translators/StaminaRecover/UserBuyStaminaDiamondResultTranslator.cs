using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain;

namespace GLOW.Core.Data.Translators.StaminaRecover
{
    public static class UserBuyStaminaDiamondResultTranslator
    {
        public static UserBuyStaminaDiamondResultModel ToUserBuyStaminaDiamondResultModel(
            UsrParameterData parameterData, 
            UsrUserBuyCountData usrUserBuyCountData)
        {
            return new UserBuyStaminaDiamondResultModel(
                UserParameterTranslator.ToUserParameterModel(parameterData),
                new UserBuyCountModel(
                    new BuyStaminaAdCount(usrUserBuyCountData.DailyBuyStaminaAdCount), 
                    usrUserBuyCountData.DailyBuyStaminaAdAt));
        }
    }
}
