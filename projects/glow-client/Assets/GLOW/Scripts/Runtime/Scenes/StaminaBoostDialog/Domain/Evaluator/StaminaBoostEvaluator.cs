using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using Zenject;

namespace GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator
{
    public class StaminaBoostEvaluator : IStaminaBoostEvaluator
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public StaminaBoostFlag HasStaminaBoost(MasterDataId stageId)
        {
            var mstStageData = MstStageDataRepository.GetMstStage(stageId);
            return HasStaminaBoost(mstStageData);
        }

        public StaminaBoostFlag HasStaminaBoost(MstStageModel mstStageModel)
        {
            if (mstStageModel.AutoLapType == null) return StaminaBoostFlag.False;

            if (mstStageModel.AutoLapType == AutoLapType.Initial) return StaminaBoostFlag.True;

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);
            var stage = GetStageClearCountable(mstQuest, mstStageModel.Id);

            return stage != null && stage.ClearCount.Value > 0
                ? StaminaBoostFlag.True
                : StaminaBoostFlag.False;
        }

        public StaminaBoostCount GetStaminaBoostCountLimit(MstStageModel mstStageModel)
        {
            // AutoLapTypeがnullの場合はスタミナブースト対象外なので1回分とする
            if (mstStageModel.AutoLapType == null) return StaminaBoostCount.One;

            // Initialの場合は最大値を返す
            if (mstStageModel.AutoLapType == AutoLapType.Initial) return mstStageModel.MaxStaminaBoostCount;

            // AutoLapType.FirstClearの場合はクリア済みかどうかで判定
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);
            var stage = GetStageClearCountable(mstQuest, mstStageModel.Id);

            return stage != null && stage.ClearCount.Value > 0
                ? mstStageModel.MaxStaminaBoostCount
                : StaminaBoostCount.One;
        }

        IStageClearCountable GetStageClearCountable(MstQuestModel mstQuest ,MasterDataId mstStageId)
        {
            IStageClearCountable stageClearCountable = null;
            if (mstQuest.QuestType == QuestType.Normal)
            {
                stageClearCountable = GameRepository.GetGameFetch().StageModels
                    .FirstOrDefault(stage => stage.MstStageId == mstStageId);
            }
            else
            {
                stageClearCountable = GameRepository.GetGameFetch().UserStageEventModels
                    .FirstOrDefault(stage => stage.MstStageId == mstStageId);
            }

            return stageClearCountable;
        }
    }
}
