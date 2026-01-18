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

            // イントロダクションステージ後のメインパート1、ステージ3クリア後のメインパート3の場合はトランジションスキップ
            return tutorialStatus.IsSkipTransitionStatus();
        }
    }
}