using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialTransitionSkipUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        
        public bool IsSkipTransition()
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;

            // イントロステージクリア後のStartMainPartステータスの場合
            return tutorialStatus.IsSkipTransitionStatus();
        }
    }
}