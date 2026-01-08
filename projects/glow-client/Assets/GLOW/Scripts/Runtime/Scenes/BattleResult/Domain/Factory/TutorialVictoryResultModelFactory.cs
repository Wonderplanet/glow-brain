using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{

    public class TutorialVictoryResultModelFactory: ITutorialVictoryResultModelFactory
    {
        [Inject] IUserExpGainModelsFactory UserExpGainModelsFactory { get; }
        [Inject] IUserLevelUpEffectModelFactory UserLevelUpEffectModelFactory { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IRandomProvider RandomProvider { get; }
        [Inject] IInGameScene InGameScene { get; }

        public VictoryResultModel CreateTutorialVictoryResultModel(
            TutorialStageEndResultModel tutorialStageEndResultModel,
            UserParameterModel prevUserParameterModel,
            MasterDataId mstStageId)
        {
            var pickupUnit = PickupPlayerUnit();

            var userExpGains = UserExpGainModelsFactory.CreateUserExpGainModels(
                tutorialStageEndResultModel.UserLevelUp,
                prevUserParameterModel.Level,
                prevUserParameterModel.Exp);

            // レベルアップ
            var userLevelUpEffectModel = UserLevelUpEffectModelFactory.Create(
                tutorialStageEndResultModel.UserLevelUp,
                prevUserParameterModel.Level,
                tutorialStageEndResultModel.UserParameterModel.Level);

            // ステージクリア報酬
            var mstStage = MstStageDataRepository.GetMstStage(mstStageId);
            var acquiredPlayerResources = CreateAcquiredPlayerResourcesForTutorial(
                tutorialStageEndResultModel.Rewards,
                mstStage);

            return new VictoryResultModel(
                pickupUnit.AssetKey,
                userExpGains,
                userLevelUpEffectModel,
                acquiredPlayerResources,
                Array.Empty<IReadOnlyList<PlayerResourceModel>>(),
                new List<UnreceivedRewardReasonType>(){UnreceivedRewardReasonType.None},
                Array.Empty<ArtworkFragmentAcquisitionModel>(),
                ResultScoreModel.Empty,
                ResultSpeedAttackModel.Empty,
                AdventBattleResultScoreModel.Empty,
                PvpBattleResultPointModel.Empty,
                InGameType.Normal,
                RemainingTimeSpan.Empty,
                InGameRetryModel.Empty);
        }

        DeckUnitModel PickupPlayerUnit()
        {
            var index = RandomProvider.Range(InGameScene.DeckUnits.Count(c => !c.IsEmptyUnit()));
            return InGameScene.DeckUnits[index];
        }

        List<PlayerResourceModel> CreateAcquiredPlayerResourcesForTutorial(
            IReadOnlyList<StageRewardResultModel> rewards,
            MstStageModel mstStage)
        {
            var playerResourceModels = new List<PlayerResourceModel>();
            // まずfirst clearの報酬を先に表示するためにAddする
            var firstClearRewards = CreateFirstClearReward(rewards);
            playerResourceModels.AddRange(firstClearRewards);

            // 固定報酬のコイン報酬を専用に表示するために個別にAddする
            var fixedCoinReward = CreateFixedCoinReward(rewards, mstStage);
            if (!fixedCoinReward.IsEmpty())
            {
                playerResourceModels.Add(PlayerResourceModelFactory.Create(
                    ResourceType.Coin,
                    MasterDataId.Empty,
                    mstStage.Coin.ToPlayerResourceAmount()));
            }

            // その後、first clearとSpeedAttackClear以外の報酬をAdd
            var alwaysRewards = CreateAlwaysRewards(rewards);
            playerResourceModels.AddRange(alwaysRewards);

            // fixedではないコイン報酬の該当のものをひとつだけ省く
            if (fixedCoinReward != StageRewardResultModel.Empty)
            {
                playerResourceModels.Remove(GetRandomCoinReward(playerResourceModels, fixedCoinReward.RewardModel.Amount));
            }

            return playerResourceModels;
        }

        PlayerResourceModel GetRandomCoinReward(
            List<PlayerResourceModel> playerResourceModels,
            PlayerResourceAmount playerResourceAmount)
        {
            return playerResourceModels
                .FirstOrDefault(playerResource =>
                        playerResource.Type == ResourceType.Coin &&
                        playerResource.Amount == playerResourceAmount &&
                        playerResource.RewardCategory == RewardCategory.Always,
                    PlayerResourceModel.Empty);
        }

        List<PlayerResourceModel> CreateFirstClearReward(IReadOnlyList<StageRewardResultModel> rewards)
        {
            var firstClearRewards = rewards
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

            return firstClearRewards.ToList();
        }

        //firstClearReward, fixedCoinReward, alwaysRewards
        StageRewardResultModel CreateFixedCoinReward(IReadOnlyList<StageRewardResultModel> rewards, MstStageModel mstStage)
        {
            var fixedCoinReward = rewards
                .Where(reward => reward.Category == RewardCategory.Always)
                .Where(reward => reward.RewardModel.ResourceType == ResourceType.Coin)
                .FirstOrDefault(r => r.RewardModel.Amount.Value == mstStage.Coin.Value, StageRewardResultModel.Empty);

            return fixedCoinReward;
        }

        List<PlayerResourceModel> CreateAlwaysRewards(IReadOnlyList<StageRewardResultModel> rewards)
        {
            var alwaysRewards = rewards
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

            return alwaysRewards.ToList();
        }
    }
}
