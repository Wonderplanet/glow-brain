using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class CheckFreePartTutorialCompletedUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        public bool CheckFreePartTutorialCompleted(TutorialFunctionName functionName)
        {
            var freePartModels = GameRepository.GetGameFetchOther().UserTutorialFreePartModels;

            // リストに存在する場合はチュートリアル完了済み
            return freePartModels.Any(t => t.TutorialFunctionName == functionName);
        }
    }
}
