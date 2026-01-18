using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class InGameRetryStaminaBoostUseCase
    {
        [Inject] IStaminaBoostEvaluator StaminaBoostEvaluator { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IUserStaminaModelFactory UserStaminaModelFactory { get; }

        public InGameRetryStaminaBoostUseCaseModel IsStaminaBoostAvailable()
        {
            var selectedStage = SelectedStageEvaluator.GetSelectedStage();

            if (selectedStage.InGameType == InGameType.AdventBattle ||
                selectedStage.InGameType == InGameType.Pvp)
            {
                return new InGameRetryStaminaBoostUseCaseModel( 
                    selectedStage.SelectedId,
                    StaminaBoostFlag.False,
                    EnoughStaminaFlag.True);    // 降臨バトル・ランクマッチはスタミナ不要なためTrue
            }
            
            var mstStageData = MstStageDataRepository.GetMstStage(selectedStage.SelectedId);
            var userStaminaModel = UserStaminaModelFactory.Create();
            var isEnoughStamina = userStaminaModel.CurrentStamina.Value >= mstStageData.StageConsumeStamina.Value
                ? EnoughStaminaFlag.True
                : EnoughStaminaFlag.False;
            return new InGameRetryStaminaBoostUseCaseModel(
                selectedStage.SelectedId,
                StaminaBoostEvaluator.HasStaminaBoost(selectedStage.SelectedId),
                isEnoughStamina);
        }
    }
}