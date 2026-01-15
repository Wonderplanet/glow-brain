using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialStageEndResultDataTranslator
    {
        public static TutorialStageEndResultModel Translate(TutorialStageEndResultData data)
        {
            var tutorialStatusName = new TutorialFunctionName(data.TutorialStatus);
            var model = new TutorialStatusModel(tutorialStatusName);
            var userParameter = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            var rewards = data.StageRewards.Select(StageRewardDataTranslator.ToStageRewardResultModel).ToList();
            var userLevelUp = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            
            var userUnitModels = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();
            var userEmblemModels = data.UsrEmblems
                .Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList();

            return new TutorialStageEndResultModel(
                model,
                userParameter,
                rewards,
                userLevelUp,
                userUnitModels,
                userItemModels,
                userEmblemModels);
        }
    }
}
