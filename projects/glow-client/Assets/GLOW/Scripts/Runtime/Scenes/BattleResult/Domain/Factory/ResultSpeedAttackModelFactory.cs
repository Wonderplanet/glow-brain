using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class ResultSpeedAttackModelFactory : IResultSpeedAttackModelFactory
    {
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstStageClearTimeRewardRepository MstStageClearTimeRewardRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public ResultSpeedAttackModel CreateSpeedAttackModel(
            GameFetchModel prevFetchModel,
            MstQuestModel mstQuest,
            MstStageModel mstStage,
            StageClearTime clearTime)
        {
            var mstInGameSpecialRule =
                MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(mstStage.Id, InGameContentType.Stage);
            if(mstInGameSpecialRule.IsEmpty() || mstInGameSpecialRule.All(mst => mst.RuleType != RuleType.SpeedAttack))
            {
                return ResultSpeedAttackModel.Empty;
            }

            var prevClearTime = EventClearTimeMs.Empty;
            if (mstQuest.QuestType == QuestType.Normal)
            {
                var mainQuestStage = prevFetchModel.StageModels.FirstOrDefault(model => model.MstStageId == mstStage.Id);
                prevClearTime = mainQuestStage?.ClearTimeMs ?? EventClearTimeMs.Empty;
            }
            else
            {
                var prevUserEventStage =
                    prevFetchModel.UserStageEventModels.FirstOrDefault(model => model.MstStageId == mstStage.Id);
                if (prevUserEventStage == null)
                {
                    prevClearTime = EventClearTimeMs.Empty;
                }
                else
                {
                    prevClearTime = prevUserEventStage.ResetClearTimeMs;
                }
            }

            var isNewRecord = prevClearTime.IsEmpty() || clearTime < prevClearTime;

            var rewards = MstStageClearTimeRewardRepository
                .GetClearTimeRewards(mstStage.Id)
                .OrderByDescending(mst => mst.UpperClearTimeMs)
                .Select(mst => CreateResultSpeedAttackRewardModel(mst, prevClearTime, clearTime))
                .ToList();

            return new ResultSpeedAttackModel(clearTime, rewards, new NewRecordFlag(isNewRecord));
        }

        ResultSpeedAttackRewardModel CreateResultSpeedAttackRewardModel(
            MstStageClearTimeRewardModel mst,
            EventClearTimeMs prevClearTime,
            StageClearTime clearTime)
        {
            var reward = PlayerResourceModelFactory.Create(
                mst.ResourceType,
                mst.ResourceId,
                mst.ResourceAmount.ToPlayerResourceAmount());

            var acquiredFlag = new AcquiredRewardFlag(!prevClearTime.IsEmpty() && prevClearTime <= mst.UpperClearTimeMs);
            var isNew = new NewRewardFlag(!acquiredFlag && clearTime <= mst.UpperClearTimeMs);
            return new ResultSpeedAttackRewardModel(reward, mst.UpperClearTimeMs, acquiredFlag, isNew);
        }
    }
}
