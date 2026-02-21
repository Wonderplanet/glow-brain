using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using Zenject;
using AdventBattleRankingResultModel = GLOW.Scenes.AdventBattleRankingResult.Domain.Models.AdventBattleRankingResultModel;
namespace GLOW.Scenes.AdventBattleRankingResult.Domain.ModelFactories
{
    public class AdventBattleRankingResultModelFactory : IAdventBattleRankingResultModelFactory
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public AdventBattleRankingResultModel CreateAdventBattleRankingResultModel(AdventBattleInfoResultModel infoResultModel)
        {
            var adventBattleResult = infoResultModel.AdventBattleResult;
            var mstAdventBattleId = adventBattleResult.MstAdventBattleId;
            if (mstAdventBattleId.IsEmpty())
            {
                return AdventBattleRankingResultModel.Empty;
            }

            var mstAdventBattle = MstAdventBattleDataRepository.GetMstAdventBattleModel(mstAdventBattleId);
            var mstAdventBattleScoreRanks = MstAdventBattleDataRepository.GetMstAdventBattleScoreRanks(mstAdventBattleId);
            var rankingRewards = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(mstAdventBattleId);

            var maxScore = adventBattleResult.MyRanking.MaxScore;
            var mstAdventBattleScoreRankModel = mstAdventBattleScoreRanks
                .OrderBy(x => x.RequiredLowerScore.Value)
                .Where(x => x.RequiredLowerScore <= adventBattleResult.MyRanking.TotalScore)
                .LastOrDefault(MstAdventBattleScoreRankModel.Empty);

            UnitImageAssetPath enemyImageAssetPath = UnitImageAssetPath.Empty;
            if (!mstAdventBattle.DisplayEnemyUnitIdFirst.IsEmpty())
            {
                var enemyUnitFirst =
                    MstEnemyCharacterDataRepository.GetEnemyCharacter(mstAdventBattle.DisplayEnemyUnitIdFirst);
                enemyImageAssetPath = UnitImageAssetPath.FromAssetKey(enemyUnitFirst.AssetKey);
            }

            var myRank = adventBattleResult.MyRanking.Rank;
            return new AdventBattleRankingResultModel(
                mstAdventBattleScoreRankModel.RankType,
                mstAdventBattleScoreRankModel.ScoreRankLevel,
                myRank,
                maxScore,
                CreateRewardList(rankingRewards, myRank),
                mstAdventBattle.BattleType,
                enemyImageAssetPath,
                adventBattleResult.MyRanking.IsExcludeRanking,
                mstAdventBattle.AdventBattleName);
        }

        IReadOnlyList<PlayerResourceModel> CreateRewardList(
            IReadOnlyList<MstAdventBattleRewardGroupModel> rewardGroups,
            AdventBattleRankingRank rank)
        {
            var rankingRewardGroupModel = rewardGroups
                .Where(x => x.RewardCategory == AdventBattleRewardCategory.Ranking)
                .MinByAboveOrEqualLowerLimit(x => x.RewardCondition.ToRankingRank().Value, rank.Value)
                ?? MstAdventBattleRewardGroupModel.Empty;

            return rankingRewardGroupModel.Rewards
                .Select(x => PlayerResourceModelFactory.Create(
                    x.ResourceType, 
                    x.ResourceId, 
                    x.ResourceAmount.ToPlayerResourceAmount()))
                .ToList();
        }

        MstAdventBattleScoreRankModel GetScoreRankModel(MstAdventBattleRewardGroupModel rewardGroupModel)
        {
            return MstAdventBattleDataRepository.GetMstAdventBattleScoreRank(
                new MasterDataId(rewardGroupModel.RewardCondition.Value));
        }
    }
}
