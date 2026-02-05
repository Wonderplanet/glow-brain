using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpChallengeStatus(
        PvpChallengeType Type,
        PvpDailyChallengeCount RemainingChallengeCount,//Typeによって無料のもItemのも入る
        PvpChallengeCount ChallengeableCount,
        PvpItemChallengeCost PvpItemChallengeCost,
        ItemAmount PvpChallengeItemAmount)
    {
        public static PvpChallengeStatus Empty { get; } = new(
            PvpChallengeType.Normal,
            PvpDailyChallengeCount.Empty,
            PvpChallengeCount.Empty,
            PvpItemChallengeCost.Empty,
            ItemAmount.Empty);

        public bool IsChallengeable()
        {
            return Type != PvpChallengeType.NotChallengeable && 0 < ChallengeableCount.Value;
        }

        public bool CanBeChallengeable()
        {
            return Type != PvpChallengeType.NotChallengeable && 0 < RemainingChallengeCount.Value;
        }

        public bool IsTicket()
        {
            return Type == PvpChallengeType.Ticket;
        }

        public QuestContentTopChallengeType ToQuestContentTopChallengeType()
        {
            return Type switch
            {
                PvpChallengeType.Normal => QuestContentTopChallengeType.Normal,
                PvpChallengeType.Ticket => QuestContentTopChallengeType.Ticket,
                _ => QuestContentTopChallengeType.Normal
            };
        }
    }
}
