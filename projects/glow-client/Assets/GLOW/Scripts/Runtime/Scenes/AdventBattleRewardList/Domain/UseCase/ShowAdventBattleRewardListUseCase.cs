using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattleRewardList.Domain.Factory;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;
using Zenject;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.UseCase
{
    public class ShowAdventBattleRewardListUseCase
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAdventBattleRewardModelFactory AdventBattleRewardModelFactory { get; }
        
        public AdventBattleRewardListModel FetchAdventBattleRewardList(MasterDataId adventBattleId)
        {
            var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(adventBattleId);
            if (mstAdventBattleModel.IsEmpty())
            {
                return AdventBattleRewardListModel.Empty;
            }
            
            var gameFetch = GameRepository.GetGameFetch();
            var userAdventBattleModel = gameFetch.UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleModel.Id,
                    UserAdventBattleModel.Empty);
            
            var mstAdventBattleRankModels = MstAdventBattleDataRepository.GetMstAdventBattleScoreRanks(adventBattleId);
            var currentRankModel = mstAdventBattleRankModels
                .Where(model => model.RequiredLowerScore <= userAdventBattleModel.TotalScore)
                .OrderByDescending(model => model.RequiredLowerScore)
                .FirstOrDefault(MstAdventBattleScoreRankModel.Empty);
            
            var personalRankingRewardModels = AdventBattleRewardModelFactory.CreatePersonalRankingRewardModels(adventBattleId);
            var personalRankRewardModels = AdventBattleRewardModelFactory.CreatePersonalRankRewardModelFromRank(
                adventBattleId,
                currentRankModel);
            
            var raidTotalScore = AdventBattleRaidTotalScore.Empty;
            if (mstAdventBattleModel.BattleType == AdventBattleType.Raid)
            {
                var raidTotalScoreModel = GameRepository.GetGameFetchOther().AdventBattleRaidTotalScoreModel;
                if (raidTotalScoreModel.MstAdventBattleId == adventBattleId)
                {
                    raidTotalScore = raidTotalScoreModel.AdventBattleRaidTotalScore;
                }
            }
            
            // 協力スコア報酬は一度以上プレイしないと獲得できないため、プレイ回数が0の場合は獲得済みとしない
            var raidTotalScoreRewardModels = mstAdventBattleModel.BattleType == AdventBattleType.Raid 
                ? AdventBattleRewardModelFactory.CreateRaidTotalScoreRewardModels(
                    adventBattleId, 
                    userAdventBattleModel.IsEmpty() ? 
                        AdventBattleRaidTotalScore.Zero : 
                        raidTotalScore)
                : new List<AdventBattleRaidTotalScoreRewardModel>();

            var remainingTime = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, mstAdventBattleModel.EndDateTime.Value);

            return new AdventBattleRewardListModel(
                mstAdventBattleModel.BattleType,
                userAdventBattleModel.TotalScore,
                raidTotalScore,
                currentRankModel.RankType,
                currentRankModel.ScoreRankLevel,
                remainingTime,
                personalRankingRewardModels,
                personalRankRewardModels,
                raidTotalScoreRewardModels
            );
        }
    }
}