using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Core.Domain.Evaluator
{
    public class StageOrderEvaluator : IStageOrderEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        
        public MstStageModel GetMaxOrderStage(IReadOnlyList<MstStageModel> mstStageModels)
        {
            return GetMaxOrderStageInternal(mstStageModels, questFilter:null);
        }
        
        public MstStageModel GetMaxOrderClearedStage()
        {
            var clearedStages = GetClearedStages();
            return GetMaxOrderStageInternal(clearedStages, questFilter:null);
        }
        
        public MstStageModel GetMaxOrderClearedStageWithNormalDifficulty()
        {
            var clearedStages = GetClearedStages();
            return GetMaxOrderStageInternal(clearedStages, quest => quest.Difficulty == Difficulty.Normal);
        }
        
        MstStageModel GetMaxOrderStageInternal(
            IReadOnlyList<MstStageModel> mstStageModels, 
            Func<MstQuestModel, bool> questFilter)
        {
            questFilter ??= _ => true;  // デフォルトでは全てのクエストを対象
            
            var stages = mstStageModels
                .Join(
                    MstQuestDataRepository.GetMstQuestModels().Where(questFilter),
                    mstStageModel => mstStageModel.MstQuestId,
                    mstQuestModel => mstQuestModel.Id,
                    (mstStageModel, mstQuestModel) => new { mstStageModel, mstQuestModel })
                .ToList();
            
            if (!stages.Any())
            {
                return MstStageModel.Empty;
            }

            // チュートリアル以外のステージを優先
            var nonTutorialStages = stages
                .Where(stage => stage.mstQuestModel.QuestType != QuestType.Tutorial)
                .ToList();
            
            if (nonTutorialStages.Any())
            {
                // チュートリアル以外のステージの中で最もSortOrderが大きいものを選択
                var stage = nonTutorialStages.MaxBy(stage => stage.mstStageModel.SortOrder);
                return stage.mstStageModel;
            }
            else
            {
                // チュートリアルステージのみの場合、その中で最もSortOrderが大きいものを選択
                var stage = stages.MaxBy(stage => stage.mstStageModel.SortOrder.Value);
                return stage.mstStageModel;
            }
        }
        
        IReadOnlyList<MstStageModel> GetClearedStages()
        {
            return GameRepository.GetGameFetch().StageModels
                .Where(stage => stage.ClearCount.IsCleared)
                .Join(
                    MstStageDataRepository.GetMstStages(),
                    userStageModel => userStageModel.MstStageId,
                    mstStageModel => mstStageModel.Id,
                    (userStageModel, mstStageModel) => mstStageModel)
                .ToList();
        }
    }
}