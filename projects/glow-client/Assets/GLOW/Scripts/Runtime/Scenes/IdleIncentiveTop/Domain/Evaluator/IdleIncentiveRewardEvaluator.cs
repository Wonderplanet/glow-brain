using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator
{
    public class IdleIncentiveRewardEvaluator : IIdleIncentiveRewardEvaluator
    {
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IStageOrderEvaluator StageOrderEvaluator { get; }

        public MstIdleIncentiveRewardModel EvaluateHighestClearedStageReward()
        {
            // StageOrderEvaluatorで対象ステージを取得
            var targetStage = StageOrderEvaluator.GetMaxOrderClearedStageWithNormalDifficulty();
            var targetStageId = targetStage.Id;
            
            if (targetStage.IsEmpty())
            {
                targetStageId = GetDefaultStageId();
            }
            
            if (targetStageId.IsEmpty())
            {
                return MstIdleIncentiveRewardModel.Empty;
            }
            
            // 対象ステージに対する探索報酬を取得
            var targetReward = MstIdleIncentiveRepository
                .GetMstIncentiveRewards()
                .FirstOrDefault(reward => reward.MstStageId == targetStageId, MstIdleIncentiveRewardModel.Empty);

            return targetReward;
        }
        
        MasterDataId GetDefaultStageId()
        {
            // 初期報酬ステージのIDを取得
            var initialStageConfig = MstConfigRepository.GetConfig(MstConfigKey.IdleIncentiveInitialRewardMstStageId);
            return initialStageConfig.Value.ToMasterDataId();
        }
    }
}