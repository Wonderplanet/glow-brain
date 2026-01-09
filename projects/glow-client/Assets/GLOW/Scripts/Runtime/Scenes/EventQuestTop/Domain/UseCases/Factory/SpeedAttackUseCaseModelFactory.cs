using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class SpeedAttackUseCaseModelFactory : ISpeedAttackUseCaseModelFactory
    {
        [Inject] IMstStageClearTimeRewardRepository MstStageClearTimeRewardRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }

        SpeedAttackUseCaseModel ISpeedAttackUseCaseModelFactory.Create(UserStageEventModel targetUserStageEventModel)
        {
            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                targetUserStageEventModel.MstStageId,
                InGameContentType.Stage);

            var isSpeedAttack = mstInGameSpecialRuleModels.Any(mst => mst.RuleType == RuleType.SpeedAttack);

            // 判定処理
            if(!isSpeedAttack) return SpeedAttackUseCaseModel.Empty;

            var rewards = MstStageClearTimeRewardRepository.GetClearTimeRewards(targetUserStageEventModel.MstStageId)
                .OrderByDescending(mst => mst.UpperClearTimeMs)
                .Select(mst => mst.UpperClearTimeMs)
                .ToList();

            if (targetUserStageEventModel.ResetClearTimeMs.IsEmpty())
            {
                return new SpeedAttackUseCaseModel(targetUserStageEventModel.ResetClearTimeMs, rewards.First());
            }
            else
            {
                var nextGoalTime = rewards
                    .FirstOrDefault(upperClearTime => upperClearTime < targetUserStageEventModel.ResetClearTimeMs, StageClearTime.Empty);
                return new SpeedAttackUseCaseModel(targetUserStageEventModel.ResetClearTimeMs, nextGoalTime);
            }
        }
    }
}
