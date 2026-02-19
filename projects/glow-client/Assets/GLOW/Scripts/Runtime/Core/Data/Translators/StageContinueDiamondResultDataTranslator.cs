using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Stage;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public class StageContinueDiamondResultDataTranslator
    {
        // StageContinueDiamondResultData -> StageContinueDiamondResultModel
        public static StageContinueDiamondResultModel ToStageContinueDiamondResultModel(StageContinueDiamondResultData data)
        {
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

            return new StageContinueDiamondResultModel(
                userParameterModel,
                new ContinueCount(data.ContinueCount)
            );
        }
    }
}
