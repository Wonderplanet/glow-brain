using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Outpost;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Data.Translators
{
    public class OutpostEnhanceResultDataTranslator
    {
        public static OutpostEnhanceResultModel ToOutpostEnhanceResultModel(EnhanceResultData data)
        {
            var userOutpostEnhanceLevelModel = new UserOutpostEnhanceLevelResultModel(
                new OutpostEnhanceLevel(data.BeforeLevel),
                new OutpostEnhanceLevel(data.AfterLevel));

            var parameterData = data.UsrParameter;
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(parameterData);

            return new OutpostEnhanceResultModel(
                userOutpostEnhanceLevelModel,
                userParameterModel);
        }

        public static OutpostChangeArtworkResultModel ToOutpostChangeArtworkResultModel(ChangeArtworkResultData data)
        {
            var userOutpost = UserOutpostDataTranslator.TranslateToModel(data.UsrOutpost);
            return new OutpostChangeArtworkResultModel(userOutpost);
        }
    }
}
