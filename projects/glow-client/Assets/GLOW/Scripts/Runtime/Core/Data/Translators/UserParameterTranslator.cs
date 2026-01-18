using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public static class UserParameterTranslator
    {
        public static UserParameterModel ToUserParameterModel(UsrParameterData data)
        {
            return new UserParameterModel(
                new UserLevel(data.Level),
                new UserExp(data.Exp),
                new Coin(data.Coin),
                new Stamina(data.Stamina),
                data.StaminaUpdatedAt,
                new FreeDiamond(data.FreeDiamond),
                new PaidDiamondIos(data.PaidDiamondIos),
                new PaidDiamondAndroid(data.PaidDiamondAndroid),
                new UserDailyBuyStamina(data.DailyBuyStaminaDiamondLimit, data.DailyBuyStaminaAdLimit));
        }
    }
}
