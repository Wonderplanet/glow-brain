using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.Applier
{
    public class UserTutorialFreePartModelsApplier : IUserTutorialFreePartModelsApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public void UpdateUserTutorialFreePartModels(TutorialFunctionName tutorialFunctionName)
        {
            var preFetchOtherModel = GameRepository.GetGameFetchOther();
            var tutorialStatus = new UserTutorialFreePartModel(tutorialFunctionName);

            var newFetchOtherModel = preFetchOtherModel with
            {
                UserTutorialFreePartModels = Update(preFetchOtherModel.UserTutorialFreePartModels, tutorialStatus)
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }

        IReadOnlyList<UserTutorialFreePartModel> Update(IReadOnlyList<UserTutorialFreePartModel> currentModels, UserTutorialFreePartModel updateModel)
        {
            return currentModels.ReplaceOrAdd( model => model.TutorialFunctionName == updateModel.TutorialFunctionName, updateModel);
        }
    }
}
