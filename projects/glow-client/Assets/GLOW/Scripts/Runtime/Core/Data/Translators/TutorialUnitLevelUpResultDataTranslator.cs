using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialUnitLevelUpResultDataTranslator
    {
        public static TutorialUnitLevelUpResultModel Translate(TutorialUnitLevelUpResultData data)
        {
            var tutorialStatusName = new TutorialFunctionName(data.TutorialStatus);
            var tutorialStatusModel = new TutorialStatusModel(tutorialStatusName);
            var unitModel = UserUnitDataTranslator.ToUserUnitModel(data.UsrUnit);
            var parameterModel = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            return new TutorialUnitLevelUpResultModel(tutorialStatusModel, unitModel, parameterModel);
        }
    }
}