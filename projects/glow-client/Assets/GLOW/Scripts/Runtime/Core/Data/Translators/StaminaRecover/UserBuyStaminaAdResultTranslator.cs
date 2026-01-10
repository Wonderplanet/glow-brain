using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain;

namespace GLOW.Core.Data.Translators.StaminaRecover
{
    public static class UserBuyStaminaAdResultTranslator
    {
        public static UserBuyStaminaAdResultModel ToUserBuyStaminaAdResultModel(
            UsrParameterData parameterData, 
            UsrUserBuyCountData usrUserBuyCountData)
        {
            return new UserBuyStaminaAdResultModel(
                UserParameterTranslator.ToUserParameterModel(parameterData),
                new UserBuyCountModel(
                    new BuyStaminaAdCount(usrUserBuyCountData.DailyBuyStaminaAdCount), 
                    usrUserBuyCountData.DailyBuyStaminaAdAt));
        }
    }
}
