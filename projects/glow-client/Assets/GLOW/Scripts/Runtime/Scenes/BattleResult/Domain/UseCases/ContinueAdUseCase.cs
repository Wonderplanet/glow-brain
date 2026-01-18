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
    public class ContinueAdUseCase
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
            var result = await StageService.ContinueAd(cancellationToken, selectedStage.SelectedStageId);

            // 広告でのコンティニュー回数更新
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserInGameStatusModel = fetchOtherModel.UserInGameStatusModel with
                {
                    ContinueAdCount = result.ContinueAdCount,
                },
            };
            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);

            // コンティニュー実行
            ContinueExecutor.Execute(selectedStage.SelectedStageId);
        }
    }
}
