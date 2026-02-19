using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialStageStartResultDataTranslator
    {
        public static TutorialStageStartResultModel Translate(TutorialStageStartResultData data)
        {
            var tutorialStatusName = new TutorialFunctionName(data.TutorialStatus);
            var tutorialStatusModel = new TutorialStatusModel(tutorialStatusName);

            var userInGameModels = UserInGameStatusDataTranslator.ToUserInGameStatusModel(data.UsrInGameStatus);

            return new TutorialStageStartResultModel(
                userInGameModels,
                tutorialStatusModel
            );
        }
    }
}
