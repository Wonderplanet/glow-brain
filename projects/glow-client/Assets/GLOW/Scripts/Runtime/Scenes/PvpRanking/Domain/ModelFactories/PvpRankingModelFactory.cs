using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpRanking.Domain.Models;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;
namespace GLOW.Scenes.PvpRanking.Domain.ModelFactories
{
    public class PvpRankingModelFactory : IPvpRankingModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstPvpDataRepository PvpDataRepository { get;}
        [Inject] IPvpUserRankStatusFactory PvpUserRankStatusFactory { get; }

        public PvpRankingElementUseCaseModel CreatePvpRankingElementUseCaseModel(
            PvpRankingResultModel pvpRankingResultModel,
            bool isPrevRanking)
        {
            if (pvpRankingResultModel.IsEmpty())
            {
                return PvpRankingElementUseCaseModel.Empty;
            }

            var userProfileModel = GameRepository.GetGameFetchOther().UserProfileModel;

            var otherUserModels = pvpRankingResultModel.OtherUserRanking
                .Select(model => CreateOtherUserModel(model, userProfileModel))
                .Where(model => !model.IsEmpty())
                .ToList();

            return new PvpRankingElementUseCaseModel(
                otherUserModels,
                CreateMyselfUserModel(
                    pvpRankingResultModel.MyRanking,
                    userProfileModel,
                    pvpRankingResultModel.OtherUserRanking,
                    isPrevRanking)
            );
        }

        PvpRankingOtherUserUseCaseModel CreateOtherUserModel(
            PvpOtherUserRankingModel otherUserRankingModel,
            UserProfileModel userProfileModel)
        {
            if (otherUserRankingModel.IsEmpty())
            {
                return PvpRankingOtherUserUseCaseModel.Empty;
            }

            // スコアが0(ランキング参加条件未達)の場合は空モデルを返す(TOP100から除外する)
            if (otherUserRankingModel.Score.IsZero())
            {
                return PvpRankingOtherUserUseCaseModel.Empty;
            }

            var emblemModel = otherUserRankingModel.MstEmblemId.IsEmpty() ?
                MstEmblemModel.Empty :
                MstEmblemRepository.GetMstEmblemFirstOrDefault(otherUserRankingModel.MstEmblemId);
            var mstCharacter = otherUserRankingModel.MstUnitId.IsEmpty() ?
                MstCharacterModel.Empty :
                MstCharacterDataRepository.GetCharacter(otherUserRankingModel.MstUnitId);
            var mstPvpRankModel = GetMstPvpRankModel(otherUserRankingModel.Score);

            return new PvpRankingOtherUserUseCaseModel(
                otherUserRankingModel.UserMyId,
                otherUserRankingModel.Name,
                otherUserRankingModel.Score,
                emblemModel.AssetKey,
                mstCharacter.AssetKey,
                otherUserRankingModel.Rank,
                IsMyselfRanking(otherUserRankingModel, userProfileModel),
                mstPvpRankModel.RankClassType,
                mstPvpRankModel.RankLevel,
                PvpUserRankStatusFactory.Create(otherUserRankingModel.Score));
        }

        PvpRankingMyselfUserUseCaseModel CreateMyselfUserModel(
            PvpMyRankingModel myRankingModel,
            UserProfileModel userProfileModel,
            IReadOnlyList<PvpOtherUserRankingModel> otherUserRankingModels,
            bool isPrevRanking)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var emblemModel = userProfileModel.MstEmblemId.IsEmpty() ?
                MstEmblemModel.Empty :
                MstEmblemRepository.GetMstEmblemFirstOrDefault(userProfileModel.MstEmblemId);
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userProfileModel.MstUnitId);
            var mstPvpRankModel = GetMstPvpRankModel(myRankingModel.Score);

            PvpRankingInEntryFlag isInEntry;
            PvpRankingCalculatingFlag calculatingRankings;
            PvpRankingAchieveRankingFlag achieveRanking;
            PvpUserRankStatus pvpUserRankStatus;
            PvpPoint score;
            if (isPrevRanking)
            {
                score = myRankingModel.Score;
                // 前回ランキングの場合は、ランキングスコアが最小のランクであれば達成済みと判断
                isInEntry = new PvpRankingInEntryFlag(!myRankingModel.Rank.IsEmpty());
                // 前回ランキングの場合は、ランキングスコアで判断
                achieveRanking = new PvpRankingAchieveRankingFlag(!score.IsZero());
                // 前回ランキングの場合は、集計中ではない
                calculatingRankings = new PvpRankingCalculatingFlag(false);
                pvpUserRankStatus = PvpUserRankStatusFactory.Create(score);
            }
            else
            {
                // 今回ランキングの場合は、ポイントが0でない場合は集計中と判断
                score = gameFetchOther.UserPvpStatusModel.Score;
                var existsMyself = !myRankingModel.Rank.IsEmpty();
                isInEntry = myRankingModel.IsExcludeRanking ?
                    PvpRankingInEntryFlag.False :
                    new PvpRankingInEntryFlag(existsMyself);
                // 今回ランキングの場合は、ランキングに存在するかどうか かつ ランキングスコアと乖離しているかどうかで判断
                var isScoreDifferent = !myRankingModel.Score.Equals(score);
                calculatingRankings = new PvpRankingCalculatingFlag(existsMyself && isScoreDifferent);
                achieveRanking = new PvpRankingAchieveRankingFlag(!score.IsZero());
                pvpUserRankStatus = PvpUserRankStatusFactory.Create(score);
            }

            return new PvpRankingMyselfUserUseCaseModel(
                userProfileModel.Name,
                score,
                emblemModel.AssetKey,
                mstCharacter.AssetKey,
                myRankingModel.Rank,
                mstPvpRankModel.RankClassType,
                mstPvpRankModel.RankLevel,
                calculatingRankings,
                isInEntry,
                myRankingModel.IsExcludeRanking,
                achieveRanking,
                pvpUserRankStatus);
        }

        MstPvpRankModel GetMstPvpRankModel(PvpPoint point)
        {
            return PvpDataRepository.GetMstPvpRanks()
                .MaxByBelowOrEqualUpperLimit(rank => rank.RequiredLowerPoint.Value, point.Value) ?? MstPvpRankModel.Empty;
        }

        PvpRankingMyselfFlag IsMyselfRanking(
            PvpOtherUserRankingModel otherUserRankingModel,
            UserProfileModel userProfileModel)
        {
            if (otherUserRankingModel.UserMyId.IsEmpty())
            {
                return PvpRankingMyselfFlag.False;
            }

            return otherUserRankingModel.UserMyId == userProfileModel.MyId ?
                PvpRankingMyselfFlag.True :
                PvpRankingMyselfFlag.False;
        }
    }
}

