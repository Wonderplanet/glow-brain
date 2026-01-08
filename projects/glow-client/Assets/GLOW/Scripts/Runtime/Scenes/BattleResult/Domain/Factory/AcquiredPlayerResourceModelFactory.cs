using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class AcquiredPlayerResourceModelFactory : IAcquiredPlayerResourceModelFactory
    {
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public List<PlayerResourceModel> CreateAcquiredPlayerResourcesForAdventBattle(
            AdventBattleEndResultModel battleResultModel,
            MstAdventBattleModel mstAdventBattleModel)
        {
            var playerResourceModels = new List<PlayerResourceModel>();
            var rewards = battleResultModel.AdventBattleClearRewardModels;

            // まずfirst clearの報酬を先に表示するためにAddする
            var firstClearRewards = rewards
                .Where(reward => reward.RewardCategory == AdventBattleClearRewardCategory.FirstClear)
                // 経験値報酬を省く
                .Where(playerResource => playerResource.ResourceType != ResourceType.Exp)
                // 同じ報酬をGroupByでIdとTypeとCategoryでまとめる
                .GroupBy(reward => new { reward.ResourceId, reward.ResourceType, reward.RewardCategory })
                .Select(group =>
                {
                    // FirstClearでGrepしてるのでCategoryもFirstClear
                    return PlayerResourceModelFactory.Create(
                        group.Key.ResourceType,
                        group.Key.ResourceId,
                        new PlayerResourceAmount(group.Sum(item => item.ResourceAmount.Value)),
                        RewardCategory.FirstClear);
                })
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value);
            playerResourceModels.AddRange(firstClearRewards);

            // 固定報酬のコイン報酬を専用に表示するために個別にAddする
            var fixedCoinReward = rewards
                .Where(reward => reward.RewardCategory == AdventBattleClearRewardCategory.Always)
                .Where(reward => reward.ResourceType == ResourceType.Coin)
                .FirstOrDefault(
                    reward => reward.ResourceAmount.Value == mstAdventBattleModel.Coin.Value,
                    AdventBattleClearRewardModel.Empty);

            var dropRewards = battleResultModel.AdventBattleDropRewardModels
                // 同じ報酬をGroupByでIdとTypeでまとめる
                .GroupBy(reward => new { reward.RewardModel.ResourceId, reward.RewardModel.ResourceType})
                .Select(group => PlayerResourceModelFactory.Create(
                    group.Key.ResourceType,
                    group.Key.ResourceId,
                    new PlayerResourceAmount(group.Sum(item => item.RewardModel.Amount.Value)),
                    RewardCategory.Always,
                    AcquiredFlag.False))
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value);
            playerResourceModels.AddRange(dropRewards);

            var rewardsWithoutFixedCoin = rewards.ToList();
            if (!fixedCoinReward.IsEmpty())
            {
                playerResourceModels.Add(PlayerResourceModelFactory.Create(
                    ResourceType.Coin,
                    MasterDataId.Empty,
                    mstAdventBattleModel.Coin.ToPlayerResourceAmount()));

                // fixedCoinRewardがある場合、fixedCoinRewardを除外したRewardsを取得
                rewardsWithoutFixedCoin.Remove(fixedCoinReward);
            }

            var alwaysRewards = rewardsWithoutFixedCoin
                .Where(playerResource => playerResource.ResourceType != ResourceType.Exp)
                // RewardCategoryがFirstClearではないものを取得
                .Where(reward => reward.RewardCategory is not AdventBattleClearRewardCategory.FirstClear)
                .Select(reward =>
                {
                    var rewardCategory = reward.RewardCategory.ToRewardCategory();
                    return PlayerResourceModelFactory.Create(
                        reward.ResourceType,
                        reward.ResourceId,
                        reward.ResourceAmount,
                        rewardCategory);
                })
                .GroupBy(reward => new { reward.Id, reward.Type, reward.RewardCategory })
                // 同じ報酬をGroupByでIdとTypeでまとめる
                .Select(group => PlayerResourceModelFactory.Create(
                    group.Key.Type,
                    group.Key.Id,
                    new PlayerResourceAmount(group.Sum(item => item.Amount.Value)),
                    group.First().RewardCategory))
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value)
                .ToList();
            playerResourceModels.AddRange(alwaysRewards);

            return playerResourceModels;
        }

        public List<PlayerResourceModel> CreateAcquiredPlayerResources(
            StageEndResultModel stageEndResultModel,
            MstStageModel mstStage)
        {
            return CreatePlayerResourceModelsFromRewards(
                stageEndResultModel.Rewards,
                mstStage);
        }

        public List<List<PlayerResourceModel>> CreateAcquiredPlayerResourcesGroupedByStaminaRap(
            StageEndResultModel stageEndResultModel,
            MstStageModel mstStage)
        {
            var groupedPlayerResourceModels = new List<List<PlayerResourceModel>>();

            var rewardsByStaminaRap = stageEndResultModel.Rewards
                .GroupBy(reward => reward.StaminaLapNumber.Value)
                .OrderBy(group => group.Key);

            foreach (var staminaRapGroup in rewardsByStaminaRap)
            {
                var playerResourceModels = CreatePlayerResourceModelsFromRewards(
                    staminaRapGroup,
                    mstStage);

                groupedPlayerResourceModels.Add(playerResourceModels);
            }

            return groupedPlayerResourceModels;
        }

        List<PlayerResourceModel> CreatePlayerResourceModelsFromRewards(
            IEnumerable<StageRewardResultModel> rewards,
            MstStageModel mstStage)
        {
            var rewardList = rewards.ToList();
            var playerResourceModels = new List<PlayerResourceModel>();

            // まずfirst clearの報酬を先に表示するためにAddする
            var firstClearRewards = rewardList
                .Where(reward => reward.Category == RewardCategory.FirstClear)
                // 経験値報酬を省く
                .Where(playerResource => playerResource.RewardModel.ResourceType != ResourceType.Exp)
                // 同じ報酬をGroupByでIdとTypeとCategoryでまとめる
                .GroupBy(reward => new { reward.RewardModel.ResourceId, reward.RewardModel.ResourceType, reward.Category })
                .Select(group => PlayerResourceModelFactory.Create(
                    group.Key.ResourceType,
                    group.Key.ResourceId,
                    new PlayerResourceAmount(group.Sum(item => item.RewardModel.Amount.Value)),
                    group.Key.Category))
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value);
            playerResourceModels.AddRange(firstClearRewards);

            // rewardList内のStaminaLapNumberが複数種類存在している場合は固定報酬のコインを別でAddしない
            var staminaLapNumbers = rewardList
                .Select(reward => reward.StaminaLapNumber.Value)
                .Distinct()
                .ToList();
            var hasMultipleStaminaLaps = staminaLapNumbers.Count > 1;

            // 固定報酬のコイン報酬を専用に表示するために個別にAddする
            var fixedCoinReward = rewardList
                .Where(reward => reward.Category == RewardCategory.Always)
                .Where(reward => reward.RewardModel.ResourceType == ResourceType.Coin)
                .FirstOrDefault(
                    reward => reward.RewardModel.Amount.Value == mstStage.Coin.Value,
                    StageRewardResultModel.Empty);

            var rewardsWithoutFixedCoin = rewardList.ToList();

            // StaminaLapNumberが1種類の場合のみ、固定コイン報酬を個別に表示
            if (!fixedCoinReward.IsEmpty() && !hasMultipleStaminaLaps)
            {
                playerResourceModels.Add(PlayerResourceModelFactory.Create(
                    ResourceType.Coin,
                    MasterDataId.Empty,
                    mstStage.Coin.ToPlayerResourceAmount()));

                // fixedCoinRewardがある場合、fixedCoinRewardを除外したRewardsを取得
                rewardsWithoutFixedCoin.Remove(fixedCoinReward);
            }

            // その後、first clearとSpeedAttackClear以外の報酬をAdd
            var alwaysRewards = rewardsWithoutFixedCoin
                .Where(reward => reward.Category != RewardCategory.FirstClear
                                && reward.Category != RewardCategory.SpeedAttackClear)
                // 経験値報酬を省く
                .Where(playerResource => playerResource.RewardModel.ResourceType != ResourceType.Exp)
                // 同じ報酬をGroupByでIdとTypeとCategoryでまとめる
                .GroupBy(reward => new { reward.RewardModel.ResourceId, reward.RewardModel.ResourceType, reward.Category })
                .Select(group => PlayerResourceModelFactory.Create(
                    group.Key.ResourceType,
                    group.Key.ResourceId,
                    new PlayerResourceAmount(group.Sum(item => item.RewardModel.Amount.Value)),
                    group.Key.Category))
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value);
            playerResourceModels.AddRange(alwaysRewards);

            return playerResourceModels;
        }
    }
}
