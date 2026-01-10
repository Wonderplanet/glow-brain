using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattleRanking.Domain.Models;
using GLOW.Scenes.AdventBattleRanking.Domain.ValueObjects;
using Zenject;
namespace GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories
{
    public class AdventBattleRankingModelFactory : IAdventBattleRankingModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public AdventBattleRankingElementUseCaseModel CreateAdventBattleRankingElementUseCaseModel(
            MasterDataId mstAdventBattleId,
            AdventBattleRankingResultModel adventBattleRankingResultModel,
            bool isEndOfEvent)
        {
            var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModel(mstAdventBattleId);
            var mstAdventBattleScoreRanks = MstAdventBattleDataRepository.GetMstAdventBattleScoreRanks(mstAdventBattleId);
            var sortedMstAdventBattleScoreRanks = SortMstAdventBattleScoreRanks(mstAdventBattleScoreRanks);
            var otherUserModels = adventBattleRankingResultModel.RankingList
                .OrderBy(x => x.Rank.Value)
                .Select(model => CreateOtherUserModel(model, sortedMstAdventBattleScoreRanks))
                .Where(model => !model.IsEmpty())
                .ToList();

            return new AdventBattleRankingElementUseCaseModel(
                otherUserModels,
                CreateMyselfUserModel(
                    adventBattleRankingResultModel.MyRanking,
                    sortedMstAdventBattleScoreRanks,
                    mstAdventBattleId,
                    adventBattleRankingResultModel.RankingList,
                    isEndOfEvent),
                mstAdventBattleModel.AdventBattleName);
        }

        IReadOnlyList<MstAdventBattleScoreRankModel> SortMstAdventBattleScoreRanks(
            IReadOnlyList<MstAdventBattleScoreRankModel> mstAdventBattleScoreRanks)
        {
            var orderEnum = new List<RankType>
            {
                RankType.Master,
                RankType.Gold,
                RankType.Silver,
                RankType.Bronze,
            };
            return mstAdventBattleScoreRanks
                .OrderBy(x => orderEnum.IndexOf(x.RankType))
                .ThenByDescending(x => x.ScoreRankLevel.Value)
                .ToList();
        }

        AdventBattleRankingOtherUserUseCaseModel CreateOtherUserModel(
            AdventBattleRankingItemModel rankingItemModel,
            IReadOnlyList<MstAdventBattleScoreRankModel> mstAdventBattleScoreRanks)
        {
            var maxScore = rankingItemModel.MaxScore;

            // スコアが0(ランキング参加条件未達)の場合は空モデルを返す(TOP100から除外する)
            if (maxScore.IsZero())
            {
                return AdventBattleRankingOtherUserUseCaseModel.Empty;
            }

            var mstAdventBattleScoreRankModel = GetMstAdventBattleScoreRank(mstAdventBattleScoreRanks, rankingItemModel.TotalScore);
            var emblemModel = rankingItemModel.MstEmblemId.IsEmpty() ?
                MstEmblemModel.Empty :
                MstEmblemRepository.GetMstEmblemFirstOrDefault(rankingItemModel.MstEmblemId);
            var mstCharacterModel = rankingItemModel.MstUnitId.IsEmpty() ?
                MstCharacterModel.Empty :
                MstCharacterDataRepository.GetCharacter(rankingItemModel.MstUnitId);
            return new AdventBattleRankingOtherUserUseCaseModel(
                rankingItemModel.UserMyId,
                rankingItemModel.UserName,
                rankingItemModel.MaxScore,
                emblemModel.AssetKey,
                mstCharacterModel.AssetKey,
                rankingItemModel.Rank,
                IsMyselfRanking(rankingItemModel),
                mstAdventBattleScoreRankModel.RankType,
                mstAdventBattleScoreRankModel.ScoreRankLevel);
        }

        AdventBattleRankingMyselfUserUseCaseModel CreateMyselfUserModel(
            AdventBattleMyRankingModel myRankingModel,
            IReadOnlyList<MstAdventBattleScoreRankModel> mstAdventBattleScoreRanks,
            MasterDataId mstAdventBattleId,
            IReadOnlyList<AdventBattleRankingItemModel> otherUserRankingModels,
            bool isEndOfEvent)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userProfileModel = gameFetchOther.UserProfileModel;
            var emblemModel = userProfileModel.MstEmblemId.IsEmpty() ?
                MstEmblemModel.Empty :
                MstEmblemRepository.GetMstEmblemFirstOrDefault(userProfileModel.MstEmblemId);
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userProfileModel.MstUnitId);
            var userAdventBattleModel = GameRepository.GetGameFetch().UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleId, UserAdventBattleModel.Empty);

            AdventBattleRankingInEntryFlag isInEntry;
            AdventBattleRankingCalculatingFlag calculatingRankings;
            AdventBattleRankingAchieveRankingFlag achieveRanking;
            AdventBattleScore maxScore;
            MstAdventBattleScoreRankModel mstAdventBattleScoreRankModel;
            if (isEndOfEvent)
            {

                maxScore = myRankingModel.MaxScore;
                // ランクがあるかどうかで判断(ランクがないなら未参加)
                isInEntry = new AdventBattleRankingInEntryFlag(!myRankingModel.Rank.IsEmpty());
                // 終了済みのランキングの場合は、ランキングスコアで判断(スコアが0なら参加条件未達成)
                achieveRanking = new AdventBattleRankingAchieveRankingFlag(!maxScore.IsZero());
                // 終了済みのランキングの場合は、集計中ではない
                calculatingRankings = new AdventBattleRankingCalculatingFlag(false);
                mstAdventBattleScoreRankModel = GetMstAdventBattleScoreRank(mstAdventBattleScoreRanks, myRankingModel.TotalScore);
            }
            else
            {
                maxScore = userAdventBattleModel.MaxScore;
                var existsMyself = !myRankingModel.Rank.IsEmpty();
                // ランクがあるかどうかで判断(除外されていた場合、またはランクがないなら未参加)
                isInEntry = myRankingModel.IsExcludeRanking ?
                    AdventBattleRankingInEntryFlag.False :
                    new AdventBattleRankingInEntryFlag(existsMyself);
                // 今回ランキングの場合は、ランキングに存在するかどうか かつ ランキングスコアと乖離しているかどうかで判断
                var isScoreDifferent = !myRankingModel.MaxScore.Equals(maxScore);
                calculatingRankings = new AdventBattleRankingCalculatingFlag(existsMyself && isScoreDifferent);
                // 今回ランキングの場合は、ランキングスコアが0であれば集計中と判断(スコアが0なら参加条件未達成)
                achieveRanking = new AdventBattleRankingAchieveRankingFlag(!maxScore.IsZero());
                mstAdventBattleScoreRankModel = GetMstAdventBattleScoreRank(mstAdventBattleScoreRanks, userAdventBattleModel.TotalScore);
            }


            return new AdventBattleRankingMyselfUserUseCaseModel(
                userProfileModel.Name,
                maxScore,
                emblemModel.AssetKey,
                mstCharacter.AssetKey,
                myRankingModel.Rank,
                mstAdventBattleScoreRankModel.RankType,
                mstAdventBattleScoreRankModel.ScoreRankLevel,
                calculatingRankings,
                isInEntry,
                myRankingModel.IsExcludeRanking,
                achieveRanking);
        }

        MstAdventBattleScoreRankModel GetMstAdventBattleScoreRank(
            IReadOnlyList<MstAdventBattleScoreRankModel> mstAdventBattleScoreRanks,
            AdventBattleScore totalScore)
        {
            return mstAdventBattleScoreRanks
                .Where(x => x.RequiredLowerScore <= totalScore)
                .FirstOrDefault(MstAdventBattleScoreRankModel.Empty);
        }

        AdventBattleRankingMyselfFlag IsMyselfRanking(AdventBattleRankingItemModel rankingModel)
        {
            if (rankingModel.UserMyId.IsEmpty())
            {
                return AdventBattleRankingMyselfFlag.False;
            }

            var userProfileModel = GameRepository.GetGameFetchOther().UserProfileModel;
            return rankingModel.UserMyId == userProfileModel.MyId ?
                AdventBattleRankingMyselfFlag.True :
                AdventBattleRankingMyselfFlag.False;
        }
    }
}
