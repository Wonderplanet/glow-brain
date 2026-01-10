using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialGachaConfirmResultDataTranslator
    {
        public static TutorialGachaConfirmResultModel Translate(TutorialGachaConfirmResultData data)
        {
            var tutorialStatusName = new TutorialFunctionName(data.TutorialStatus);
            var tutorialStatusModel = new TutorialStatusModel(tutorialStatusName);
            var userUnitModels = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var userParam = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);

            return new TutorialGachaConfirmResultModel(
                tutorialStatusModel,
                userUnitModels,
                userItemModels,
                userParam
            );
        }
    }
}
