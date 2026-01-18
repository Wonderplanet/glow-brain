using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class SkipIntroductionTutorialUseCase
    {
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] ITutorialStatusApplier TutorialStatusApplier { get; }
        
        public async UniTask SkipIntroductionTutorial(CancellationToken cancellationToken)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;

            // 導入チュートリアル報酬受け取り後は以下の処理はしない
            if (!tutorialStatus.IsIntroduction()) return;
            
            var mstTutorialModels = MstTutorialRepository.GetMstTutorialModels();
            var currentTutorial = mstTutorialModels
                .FirstOrDefault(x => x.TutorialFunctionName == tutorialStatus.TutorialFunctionName, MstTutorialModel.Empty);

            var nextTutorialModel = mstTutorialModels
                .Where(x => x.TutorialType != TutorialType.Free)
                .MinByAboveLowerLimit(x => x.SortOrder.Value, currentTutorial.SortOrder.Value) ?? MstTutorialModel.Empty;

            // 次のチュートリアルが存在しない場合は例外を投げる
            if(nextTutorialModel.IsEmpty()) throw new Exception("次のTutorialStatusが存在しません");

            var newtTutorialStatus = new TutorialStatusModel(nextTutorialModel.TutorialFunctionName);
            
            var result = await TutorialService.EndTutorialStage(cancellationToken, newtTutorialStatus.TutorialFunctionName);
            
            var prevGameFetchModel = GameRepository.GetGameFetch();
            var newGameFetch = prevGameFetchModel with
            {
                UserParameterModel = result.UserParameterModel
            };
            GameManagement.SaveGameFetch(newGameFetch);
            TutorialStatusApplier.UpdateTutorialStatus(result.TutorialStatusModel);
        }
    }
}