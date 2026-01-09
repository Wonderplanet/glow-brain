using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Modules.Tutorial.Domain.Evaluator
{
    public static class TutorialEvaluator
    {
        public static MstTutorialModel GetNextMstTutorialModel(IGameRepository gameRepository, IMstTutorialRepository tutorialRepository)
        {
            var prevFetchOtherModel = gameRepository.GetGameFetchOther();
            var mstTutorialModels = tutorialRepository.GetMstTutorialModels();
            var currentTutorial = mstTutorialModels
                .FirstOrDefault(x => x.TutorialFunctionName == prevFetchOtherModel.TutorialStatus.TutorialFunctionName, MstTutorialModel.Empty);

            // チュートリアルが存在しない場合は例外を投げる
            if(currentTutorial.IsEmpty()) throw new Exception("TutorialStatusが不正です");

            var nextTutorialModel = mstTutorialModels
                .Where(x => x.TutorialType != TutorialType.Free)
                .MinByAboveLowerLimit(x => x.SortOrder.Value, currentTutorial.SortOrder.Value) ?? MstTutorialModel.Empty;

            // 次のチュートリアルが存在しない場合は例外を投げる
            if(nextTutorialModel.IsEmpty()) throw new Exception("次のTutorialStatusが存在しません");
            
            return nextTutorialModel;
        }
        
        public static bool CanBeginTutorial(
            IMstTutorialRepository mstTutorialRepository,
            IGameRepository gameRepository,
            IReadOnlyList<UserTutorialFreePartModel> models, 
            TutorialFunctionName functionName, 
            Func<bool> functionCondition = null)
        {
            // リスト内にModelがある場合は完了済みのためfalseを返す
            if (models.Any(m => m.TutorialFunctionName == functionName)) return false;

            // 開催状態など、チュートリアル開始条件を満たしていない場合はfalseを返す
            if (functionCondition != null && !functionCondition!.Invoke()) return false;

            // チュートリアルの開始条件を満たしていない場合はfalseを返す
            if(!CheckTutorialCondition(functionName, mstTutorialRepository, gameRepository)) return false;

            return true;
        }

        static bool CheckTutorialCondition(
            TutorialFunctionName functionName,
            IMstTutorialRepository mstTutorialRepository,
            IGameRepository gameRepository)
        {
            var tutorialModel = mstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == functionName, MstTutorialModel.Empty);

            if(tutorialModel.IsEmpty()) return false;

            return (tutorialModel.ConditionType) switch {
                TutorialConditionType.UserLevel => CheckTutorialConditionByLevel(tutorialModel.ConditionValue, gameRepository),
                TutorialConditionType.StageClear => CheckTutorialConditionByStage(tutorialModel.ConditionValue, gameRepository),
                _ => false
            };
        }
        
        // 条件レベルが足りているか
        static bool CheckTutorialConditionByLevel(TutorialConditionValue value, IGameRepository gameRepository)
        {
            var requiredLevel = value.ToUserLevel();
            if (requiredLevel.IsEmpty())
            {
                return false;
            }

            var userLevel = gameRepository.GetGameFetch().UserParameterModel.Level;

            return userLevel >= requiredLevel;
        }
        
        // 条件ステージをクリアしているか
        static bool CheckTutorialConditionByStage(TutorialConditionValue value, IGameRepository gameRepository)
        {
            var stageModels = gameRepository.GetGameFetch().StageModels;
            var mstStageId = value.ToMasterDataId();
            var model = stageModels.FirstOrDefault(m => m.MstStageId == mstStageId, StageModel.Empty);

            // ステージが存在し、クリアしているか
            return !model.IsEmpty() && model.ClearCount >= 1;
        }
    }
}