using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.PvpRewardList.Domain.Factory
{
    public class PvpRewardModelFactory : IPvpRewardModelFactory
    {
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        [Inject] IGameRepository GameRepository { get; }
        
        IReadOnlyList<IPvpRankingRewardModel> IPvpRewardModelFactory.CreateRankingRewardModels(ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpRewardGroupModels = MstCurrentPvpModelResolver.CreateMstPvpRewardGroups(sysPvpSeasonId);
            var rankingRewards = mstPvpRewardGroupModels
                .Where(group => group.RewardCategory == PvpRewardCategory.Ranking)
                .OrderByDescending(model => model.ConditionValue.ToPvpRankingRank())
                .ToList();
            
            // 1位の報酬を取得
            var firstReward = rankingRewards.LastOrDefault(MstPvpRewardGroupModel.Empty);
            
            // ランキング報酬を取得
            var firstRewardModel = CreateSingleRankingRewardModel(firstReward);
            
            var otherRewardModels = rankingRewards.PairWithNext()
                .Select(pair =>
                {
                    var lowerRank = pair.current.ConditionValue.ToPvpRankingRank();
                    var upperRank = pair.next.ConditionValue.ToPvpRankingRank();
                    if (lowerRank - upperRank == PvpRankingRank.One)
                    {
                        return CreateSingleRankingRewardModel(pair.current) as IPvpRankingRewardModel;
                    }
                    else
                    {
                        // 区分が連続していない場合
                        return CreatePvpIntervalRankingRewardModel(pair.current, pair.next);
                    }
                })
                .Reverse();
            
            return new List<IPvpRankingRewardModel>{firstRewardModel}.Concat(otherRewardModels).ToList();
        }

        IReadOnlyList<PvpPointRankRewardModel> IPvpRewardModelFactory.CreatePvpPointRankRewardModels(ContentSeasonSystemId sysPvpSeasonId)
        {
            var rankModels = MstPvpDataRepository.GetMstPvpRanks();
            var mstPvpRewardGroupModels = MstCurrentPvpModelResolver.CreateMstPvpRewardGroups(sysPvpSeasonId);
            var rankRewards = mstPvpRewardGroupModels
                .Where(group => group.RewardCategory == PvpRewardCategory.RankClass)
                .ToList();
            
            var table = rankRewards
                .Join(rankModels,
                    group => group.ConditionValue.ToMasterDataId(),
                    rank => rank.Id,
                    (group, rank) => new
                    {
                        group,
                        rank
                    });

            var result = table.Select(
                    model => CreatePointRankRewardModel(
                        model.group,
                        model.rank))
                .OrderByDescending(model => model.RequiredPoint)
                .ToList();
            
            return result;
        }

        IReadOnlyList<PvpTotalScoreRewardModel> IPvpRewardModelFactory.CreatePvpTotalScoreRewardModels(
            ContentSeasonSystemId sysPvpSeasonId)
        {
            var mstPvpRewardGroupModels = MstCurrentPvpModelResolver.CreateMstPvpRewardGroups(sysPvpSeasonId);

            var totalScoreRewardModels = mstPvpRewardGroupModels
                .Where(group => group.RewardCategory == PvpRewardCategory.TotalScore)
                .Select(CreateTotalScoreRewardModel)
                .OrderByDescending(model => model.RequiredPoint)
                .ToList();
            
            return totalScoreRewardModels;
        }
        
        PvpSingleRankingRewardModel CreateSingleRankingRewardModel(
            MstPvpRewardGroupModel model)
        {
            var rewards = model.Rewards.Select(CreatePlayerResourceModel).ToList();
            
            return new PvpSingleRankingRewardModel(
                model.Id,
                rewards,
                model.ConditionValue.ToPvpRankingRank());
        }
        
        PvpIntervalRankingRewardModel CreatePvpIntervalRankingRewardModel(
            MstPvpRewardGroupModel currentModel,
            MstPvpRewardGroupModel nextModel)
        {
            var lowerRank = currentModel.ConditionValue.ToPvpRankingRank();
            var upperRank = nextModel.ConditionValue.ToPvpRankingRank();
            
            // 区分が連続していない場合は上端の区分を1つ下げる(データ設定されている順位は下端になるため)
            upperRank += PvpRankingRank.One;
            
            var rewards = currentModel.Rewards.Select(CreatePlayerResourceModel).ToList();
            
            return new PvpIntervalRankingRewardModel(
                currentModel.Id,
                rewards,
                lowerRank,
                upperRank);
        }
        
        PvpPointRankRewardModel CreatePointRankRewardModel(
            MstPvpRewardGroupModel model,
            MstPvpRankModel rank)
        {
            return new PvpPointRankRewardModel(
                model.Id,
                model.Rewards.Select(CreatePlayerResourceModel).ToList(),
                rank.RankClassType,
                rank.RankLevel, 
                rank.RequiredLowerPoint);
        }
        
        PlayerResourceModel CreatePlayerResourceModel(MstPvpRewardModel model)
        {
            return PlayerResourceModelFactory.Create(
                model.ResourceType,
                model.ResourceId,
                model.Amount.ToPlayerResourceAmount());
        }
        
        PvpTotalScoreRewardModel CreateTotalScoreRewardModel(MstPvpRewardGroupModel model)
        {
            var totalScore = GameRepository.GetGameFetchOther().UserPvpStatusModel.MaxReceivedTotalScore;
            var isReceived = totalScore >= model.ConditionValue.ToPvpPoint();
            
            return new PvpTotalScoreRewardModel(
                model.Id,
                model.Rewards.Select(CreatePlayerResourceModel).ToList(),
                model.ConditionValue.ToPvpPoint(),
                new PvpRewardReceivedFlag(isReceived));
        }
    }
}