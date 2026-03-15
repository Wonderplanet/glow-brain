using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.Applier
{
    public class TutorialStatusApplier : ITutorialStatusApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public void UpdateTutorialStatus(TutorialStatusModel tutorialStatus)
        {

            var preFetchOtherModel = GameRepository.GetGameFetchOther();

            var newFetchOtherModel = preFetchOtherModel with
            {
                TutorialStatus = tutorialStatus
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }

        public void UpdateTutorialStageEndResult(TutorialStageEndResultModel result)
        {
            var prevGameFetchModel = GameRepository.GetGameFetch();
            var prevGameFetchOther = GameRepository.GetGameFetchOther();
            
            var newGameFetch = prevGameFetchModel with
            {
                UserParameterModel = result.UserParameterModel
            };
            
            var newGameFetchOther = prevGameFetchOther with
            {
                TutorialStatus = result.TutorialStatusModel,
                UserUnitModels = prevGameFetchOther.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = prevGameFetchOther.UserItemModels.Update(result.UserItemModels),
                UserEmblemModel = prevGameFetchOther.UserEmblemModel.Update(result.UserEmblemModels)
            };
            
            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);
        }
    }
}
