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
    }
}
