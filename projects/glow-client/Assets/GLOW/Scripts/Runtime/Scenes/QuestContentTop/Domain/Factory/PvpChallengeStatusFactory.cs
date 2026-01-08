using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public class PvpChallengeStatusFactory : IPvpChallengeStatusFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        PvpChallengeStatus IPvpChallengeStatusFactory.Create(
            PvpItemChallengeCost pvpItemChallengeCost,
            UserPvpStatusModel userPvpStatusModel)
        {
            //通常あれば、通常返す
            if (1 <= userPvpStatusModel.RemainingChallengeCount.Value)
            {
                return new PvpChallengeStatus(
                    PvpChallengeType.Normal,
                    userPvpStatusModel.RemainingChallengeCount,
                    new PvpChallengeCount(userPvpStatusModel.RemainingChallengeCount.Value),
                    PvpItemChallengeCost.Zero,
                    ItemAmount.Empty);
            }

            return CreateStatusByItem(pvpItemChallengeCost, userPvpStatusModel);
        }

        PvpChallengeStatus CreateStatusByItem(
            PvpItemChallengeCost pvpItemChallengeCost,
            UserPvpStatusModel userPvpStatusModel)
        {
            var targetMstItemId = MstConfigRepository.GetConfig(MstConfigKey.PvpChallengeItemId).Value;
            var targetUserItem = GameRepository.GetGameFetchOther().UserItemModels
                .FirstOrDefault(x => x.MstItemId.Value == targetMstItemId.Value,
                    UserItemModel.Empty);

            //itemChallengeCountない
            if (userPvpStatusModel.RemainingItemChallengeCount.Value <= 0)
            {
                return new PvpChallengeStatus(
                    PvpChallengeType.NotChallengeable,
                    userPvpStatusModel.RemainingItemChallengeCount,
                    PvpChallengeCount.Empty,
                    pvpItemChallengeCost,
                    targetUserItem.Amount);
            }

            // アイテムない
            if (targetUserItem.IsEmpty() || targetUserItem.Amount.IsZero())
            {
                return new PvpChallengeStatus(
                    PvpChallengeType.Ticket,
                    userPvpStatusModel.RemainingItemChallengeCount,
                    PvpChallengeCount.Empty,
                    pvpItemChallengeCost,
                    targetUserItem.Amount);
            }

            var itemCostForAllChallenges =
                userPvpStatusModel.RemainingItemChallengeCount.Value * pvpItemChallengeCost.Value;

            // アイテムの方が多い -> itemChallengeCount返す
            if (targetUserItem.Amount.Value>= itemCostForAllChallenges)
            {
                return new PvpChallengeStatus(
                    PvpChallengeType.Ticket,
                    userPvpStatusModel.RemainingItemChallengeCount,
                    new PvpChallengeCount(userPvpStatusModel.RemainingItemChallengeCount.Value),
                    pvpItemChallengeCost,
                    targetUserItem.Amount);
            }

            // ChallengeCountの方が多い -> アイテムの数量分返す
            var itemChallengeCount = targetUserItem.Amount.Value / pvpItemChallengeCost.Value;
            return new PvpChallengeStatus(
                PvpChallengeType.Ticket,
                userPvpStatusModel.RemainingItemChallengeCount,
                new PvpChallengeCount(itemChallengeCount),
                pvpItemChallengeCost,
                targetUserItem.Amount);
        }
    }
}

