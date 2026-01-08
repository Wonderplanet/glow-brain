using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Stage;

namespace GLOW.Core.Data.Translators
{
    public static class StageStartResultTranslator
    {
        public static StageStartResultModel ToStageStartResultModel(StageStartResultData data)
        {
            var paramModel = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var statusModel = UserInGameStatusDataTranslator.ToUserInGameStatusModel(data.UsrInGameStatus);
            return new StageStartResultModel(paramModel, statusModel);
        }
    }
}
