using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;
using GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Factory
{
    public class AdventBattleRewardModelFactory : IAdventBattleRewardModelFactory
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        IReadOnlyList<IAdventBattlePersonalRewardModel> IAdventBattleRewardModelFactory.CreatePersonalRankRewardModelFromRank(
            MasterDataId adventBattleId,
            MstAdventBattleScoreRankModel currentRankModel)
        {
            var rankModels = MstAdventBattleDataRepository.GetMstAdventBattleScoreRanks(adventBattleId);
            var mstAdventBattleRewardGroupModel = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(adventBattleId);
            var rankRewards = mstAdventBattleRewardGroupModel
                .Where(model => model.RewardCategory == AdventBattleRewardCategory.Rank).ToList();

            var table = rankRewards
                .Join(rankModels,
                    group => group.RewardCondition.ToMasterDataId(),
                    rank => rank.Id,
                    (group, rank) => new
                    {
                        group,
                        rank
                    });
            
            var result = table.Select(
                    model => CreateAdventBattleScoreRankRewardModel(model.group, model.rank, currentRankModel))
                .OrderByDescending(model => model.RewardRankLowerScore)
                .ToList();

            return result;
        }

        IReadOnlyList<IAdventBattlePersonalRewardModel> IAdventBattleRewardModelFactory.CreatePersonalRankingRewardModels(
            MasterDataId adventBattleId)
        {
            var mstAdventBattleRewardGroupModel = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(adventBattleId);
            var rankingReward = mstAdventBattleRewardGroupModel
                .Where(group => group.RewardCategory == AdventBattleRewardCategory.Ranking)
                .OrderByDescending(model => model.RewardCondition.ToRankingRank())
                .ToList();
            
            // 1位の報酬を取得
            var firstReward = rankingReward
                .LastOrDefault(MstAdventBattleRewardGroupModel.Empty);
            
            var firstRewardModel = CreateAdventBattleSingleRankingRewardModel(firstReward);
            
            // 2位以降の報酬を取得(2つずつの要素を取って順位区分を取得するため)
            var otherRewardModel = rankingReward.PairWithNext()
                .Select(pair =>
                {
                    var lowerRank = pair.current.RewardCondition.ToRankingRank();
                    var upperRank = pair.next.RewardCondition.ToRankingRank();
                    if (lowerRank - upperRank == AdventBattleRankingRank.One)
                    {
                        return CreateAdventBattleSingleRankingRewardModel(pair.current) as IAdventBattlePersonalRewardModel;
                    }
                    else
                    {
                        // 区分が連続していない場合
                        return CreateAdventBattleIntervalRankingRewardModel(pair.current, pair.next);
                    }
                })
                .Reverse();
            
            return new List<IAdventBattlePersonalRewardModel>{firstRewardModel}.Concat(otherRewardModel).ToList();
        }

        IReadOnlyList<AdventBattleRaidTotalScoreRewardModel> IAdventBattleRewardModelFactory.CreateRaidTotalScoreRewardModels(
            MasterDataId adventBattleId,
            AdventBattleRaidTotalScore raidTotalScore)
        {
            var mstAdventBattleRewardGroupModel = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(adventBattleId);
            var raidTotalScoreReward = mstAdventBattleRewardGroupModel
                .Where(group => group.RewardCategory == AdventBattleRewardCategory.RaidTotalScore).ToList();
            
            return raidTotalScoreReward.Select(
                    model => CreateAdventBattleRaidTotalScoreRewardModel(model, raidTotalScore))
                .OrderByDescending(model => model.RewardCondition.ToAdventBattleScore())
                .ToList();
        }
        
        AdventBattleScoreRankRewardModel CreateAdventBattleScoreRankRewardModel(
            MstAdventBattleRewardGroupModel group,
            MstAdventBattleScoreRankModel rank,
            MstAdventBattleScoreRankModel currentRankModel)
        {
            var isReceived = rank.RequiredLowerScore <= currentRankModel.RequiredLowerScore;
            return new AdventBattleScoreRankRewardModel(
                group.Id,
                group.Rewards.Select(CreatePlayerResourceModel).ToList(),
                rank.RankType,
                rank.ScoreRankLevel,
                rank.RequiredLowerScore,
                new AdventBattleRewardReceivedFlag(isReceived));
        }

        AdventBattleSingleRankingRewardModel CreateAdventBattleSingleRankingRewardModel(
            MstAdventBattleRewardGroupModel model)
        {
            var rewards = model.Rewards.Select(CreatePlayerResourceModel).ToList();
            
            return new AdventBattleSingleRankingRewardModel(
                model.Id,
                rewards,
                model.RewardCondition.ToRankingRank());
        }
        
        AdventBattleIntervalRankingRewardModel CreateAdventBattleIntervalRankingRewardModel(
            MstAdventBattleRewardGroupModel currentModel,
            MstAdventBattleRewardGroupModel nextModel)
        {
            var lowerRank = currentModel.RewardCondition.ToRankingRank();
            var upperRank = nextModel.RewardCondition.ToRankingRank();
            
            // 区分が連続していない場合は上端の区分を1つ下げる(データ設定されている順位は下端になるため)
            upperRank += AdventBattleRankingRank.One;
            
            var rewards = currentModel.Rewards.Select(CreatePlayerResourceModel).ToList();
            
            return new AdventBattleIntervalRankingRewardModel(
                currentModel.Id,
                rewards,
                lowerRank,
                upperRank);
        }
        
        AdventBattleRaidTotalScoreRewardModel CreateAdventBattleRaidTotalScoreRewardModel(
            MstAdventBattleRewardGroupModel model,
            AdventBattleRaidTotalScore currentRaidTotalScore)
        {
            var isReceived = model.RewardCondition.ToAdventBattleRaidTotalScore() <= currentRaidTotalScore;
            return new AdventBattleRaidTotalScoreRewardModel(
                model.Id,
                model.Rewards.Select(CreatePlayerResourceModel).ToList(),
                model.RewardCondition,
                new AdventBattleRewardReceivedFlag(isReceived));
        }
        
        PlayerResourceModel CreatePlayerResourceModel(MstAdventBattleRewardModel model)
        {
            return PlayerResourceModelFactory.Create(
                model.ResourceType,
                model.ResourceId,
                model.ResourceAmount.ToPlayerResourceAmount());
        }
    }
}