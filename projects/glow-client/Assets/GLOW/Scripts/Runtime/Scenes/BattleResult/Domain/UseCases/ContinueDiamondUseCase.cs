using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Scenes.BattleResult.Domain.Executors;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class ContinueDiamondUseCase
    {
        [Inject] IStageService StageService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IContinueExecutor ContinueExecutor { get; }

        public async UniTask Continue(CancellationToken cancellationToken)
        {
            // API処理
            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();

            var result = await StageService.ContinueDiamond(cancellationToken, selectedStage.SelectedStageId);
            var fetchModel = GameRepository.GetGameFetch();
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = result.UserParameterModel,
            };
            GameManagement.SaveGameFetch(updatedFetchModel);

            ContinueExecutor.Execute(selectedStage.SelectedStageId);
        }
    }
}
