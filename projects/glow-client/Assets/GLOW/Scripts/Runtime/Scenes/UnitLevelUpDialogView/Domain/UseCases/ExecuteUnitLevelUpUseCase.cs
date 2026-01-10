using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.UnitLevelUpDialogView.Domain.UseCases
{
    public class ExecuteUnitLevelUpUseCase
    {
        [Inject] IUnitService UnitService { get; }
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }

        public async UniTask ExecuteUnitLevelUp(CancellationToken cancellationToken, UserDataId userUnitId, UnitLevel selectLevel)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            if (tutorialStatus.IsStartMainPart2())
            {
                // 強化チュートリアル
                var nextMstTutorialModel = TutorialEvaluator.GetNextMstTutorialModel(GameRepository, MstTutorialRepository);
                
                var result = await TutorialService.TutorialUnitLevelUp(
                    cancellationToken, 
                    nextMstTutorialModel.TutorialFunctionName,
                    userUnitId, 
                    selectLevel);
                UpdateTutorialGameModel(result);
            }
            else
            {
                var result = await UnitService.LevelUp(cancellationToken, userUnitId, selectLevel);
                UpdateGameModel(result.UserUnit, result.UserParameter);
            }
        }

        void UpdateGameModel(UserUnitModel unit, UserParameterModel userParameter)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetch = gameFetch with
            {
                UserParameterModel = userParameter,
            };

            var newGameFetchOther = gameFetchOther with
            {
                UserUnitModels = gameFetchOther.UserUnitModels.Update(unit)
            };

            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);
        }

        void UpdateTutorialGameModel(TutorialUnitLevelUpResultModel tutorialResultModel)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetch = gameFetch with
            {
                UserParameterModel = tutorialResultModel.UserParameterModel,
            };

            var newGameFetchOther = gameFetchOther with
            {
                TutorialStatus = tutorialResultModel.TutorialStatusModel,
                UserUnitModels = gameFetchOther.UserUnitModels.Update(tutorialResultModel.UserUnitModel)
            };

            GameManagement.SaveGameUpdateAndFetch(newGameFetch, newGameFetchOther);
        }
    }
}
