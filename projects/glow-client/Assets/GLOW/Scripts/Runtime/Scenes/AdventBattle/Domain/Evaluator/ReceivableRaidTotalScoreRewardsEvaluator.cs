using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.Evaluator
{
    public class ReceivableRaidTotalScoreRewardsEvaluator : IReceivableRaidTotalScoreRewardsEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IAdventBattlePreferenceRepository AdventBattlePreferenceRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        
        bool IReceivableRaidTotalScoreRewardsEvaluator.ExistsReceivableRaidTotalScoreRewards()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            
            var currentRaidTotalScoreModel = gameFetchOther.AdventBattleRaidTotalScoreModel;
            if (currentRaidTotalScoreModel.IsEmpty())
            {
                return false;
            }
            
            var beforeRaidTotalScoreModel = AdventBattlePreferenceRepository.EvaluatedRaidTotalScoreModelForRewards;
            
            // 前回の協力スコアが異なる場合は使わない
            if (beforeRaidTotalScoreModel.MstAdventBattleId != currentRaidTotalScoreModel.MstAdventBattleId)
            {
                beforeRaidTotalScoreModel = AdventBattleRaidTotalScoreModel.Empty;
            }
            
            if (beforeRaidTotalScoreModel.AdventBattleRaidTotalScore == currentRaidTotalScoreModel.AdventBattleRaidTotalScore)
            {
                return false;
            }
            
            // 前回判定時の協力スコアから今の協力スコアまでの報酬を取得
            var receivableRaidTotalScoreRewards = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(
                currentRaidTotalScoreModel.MstAdventBattleId)
                .Where(model => model.RewardCategory == AdventBattleRewardCategory.RaidTotalScore)
                .Where(model => model.RewardCondition.ToAdventBattleRaidTotalScore() <= currentRaidTotalScoreModel.AdventBattleRaidTotalScore)
                .Where(model => model.RewardCondition.ToAdventBattleRaidTotalScore() > beforeRaidTotalScoreModel.AdventBattleRaidTotalScore);
            
            // 報酬があればtrueを返す
            return receivableRaidTotalScoreRewards.Any();
        }
    }
}