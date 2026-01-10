using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.GachaList.Domain.Applier;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class CompleteFreePartTutorialUseCase
    {
        [Inject] ITutorialService _tutorialService;
        [Inject] IUserTutorialFreePartModelsApplier UserTutorialFreePartModelsApplier { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask CompleteFreePartTutorial(CancellationToken cancellationToken, TutorialFunctionName functionName)
        {
            // サーバー チュートリアル進捗を保存
            var result = await _tutorialService.UpdateTutorialStatus(cancellationToken, functionName);
            
            var preFetchOtherModel = GameRepository.GetGameFetchOther();
            var newFetchOtherModel = preFetchOtherModel with
            {
                UserGachaModels = preFetchOtherModel.UserGachaModels.Update(result.UserGachaModels)
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);

            // クライアント チュートリアル進捗を保存
            UserTutorialFreePartModelsApplier.UpdateUserTutorialFreePartModels(functionName);
        }
    }
}
