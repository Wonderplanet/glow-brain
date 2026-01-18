using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public class PvpTopUserStateFactory : IPvpTopUserStateFactory
    {
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }
        [Inject] IPvpChallengeStatusFactory PvpChallengeStatusFactory { get; }
        [Inject] IPvpUserRankStatusFactory PvpUserRankStatusFactory { get; }

        PvpTopUserState IPvpTopUserStateFactory.Create(
            PvpTopRankingState pvpRankingState, 
            MstPvpModel mstPvpModel, 
            UserPvpStatusModel userPvpStatusModel)
        {
            var userRankStatus =
                PvpUserRankStatusFactory.Create(userPvpStatusModel.Score);

            return  new PvpTopUserState(
                CreatePvpRankingUserJoinType(
                    pvpRankingState.PvpRankingTargetType,
                    mstPvpModel.MinPvpRankClass,
                    userRankStatus.PvpRankClassType),
                userPvpStatusModel.Score,
                CreateNextRankUpPoint(userPvpStatusModel.Score),
                userRankStatus,
                PvpChallengeStatusFactory.Create(mstPvpModel.ItemChallengeCost, userPvpStatusModel)
            );
        }

        PvpRankingUserJoinType CreatePvpRankingUserJoinType(
            PvpRankingTargetType targetType,
            PvpRankClassType? pvpRankClassType,
            PvpRankClassType userRankType)
        {
            if (targetType == PvpRankingTargetType.None || pvpRankClassType == null) return PvpRankingUserJoinType.CannotJoin;
            if (targetType == PvpRankingTargetType.AllRank) return PvpRankingUserJoinType.CanJoin;

            // ユーザのランクが指定されたランク以上なら参加可能
            return pvpRankClassType <= userRankType
                ? PvpRankingUserJoinType.CanJoin
                : PvpRankingUserJoinType.CannotJoin;
        }

        PvpPoint CreateNextRankUpPoint(PvpPoint point)
        {
            // ランクアップに必要なポイントを計算
            var nextPvpRankModel = MstPvpDataRepository.GetNextPvpRankModel(point);
            if (nextPvpRankModel.IsEmpty())
            {
                return PvpPoint.Empty;
            }

            var nextPoint = (int)nextPvpRankModel.RequiredLowerPoint.Value;
            var currentPoint = (int)point.Value;

            return new PvpPoint(nextPoint - currentPoint);
        }


    }
}
