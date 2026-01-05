using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class ShouldTutorialSetNameUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] ITutorialService TutorialService { get; }

        public async UniTask<bool> ShouldTutorialSetName(CancellationToken cancellationToken)
        {
            // 名前決定後のチュートリアルステータス更新で失敗した場合に更新する
            var userName = GameRepository.GetGameFetchOther().UserProfileModel.Name;
            if (!userName.IsEmpty() && GameRepository.GetGameFetchOther().TutorialStatus.ShouldSetName())
            {
                // 名前設定がされているためチュートリアルステータスを更新してfalseを返す
                await ProgressTutorialStatus(cancellationToken);
                return false;
            }
            
            return GameRepository.GetGameFetchOther().TutorialStatus.ShouldSetName();
        }

        async UniTask ProgressTutorialStatus(CancellationToken cancellationToken)
        {
            var nextTutorialModel = TutorialEvaluator.GetNextMstTutorialModel(GameRepository, MstTutorialRepository);
            
            var result = await TutorialService.UpdateTutorialStatus(cancellationToken, nextTutorialModel.TutorialFunctionName);

            var newTutorialStatus = new TutorialStatusModel(nextTutorialModel.TutorialFunctionName);
            var prevFetchOtherModel = GameRepository.GetGameFetchOther();
            var newFetchOtherModel = prevFetchOtherModel with
            {
                TutorialStatus = newTutorialStatus,
                UserGachaModels = prevFetchOtherModel.UserGachaModels.Update(result.UserGachaModels),
                UserIdleIncentiveModel = result.UserIdleIncentiveModel.IsEmpty()
                    ? prevFetchOtherModel.UserIdleIncentiveModel
                    : result.UserIdleIncentiveModel
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }
    }
}
