using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class CheckTutorialCompletedUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public bool CheckTutorialCompleted()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            return fetchOtherModel.TutorialStatus == TutorialSequenceIdDefinitions.TutorialMainPart_completeTutorial;
        }
    }
}
