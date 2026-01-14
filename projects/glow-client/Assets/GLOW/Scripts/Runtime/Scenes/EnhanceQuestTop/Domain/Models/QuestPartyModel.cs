using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Models
{
    public record QuestPartyModel(
        PartyName PartyName,
        EventBonusPercentage TotalBonusPercentage
    )
    {
        public static QuestPartyModel Empty { get; } = new(
            PartyName.Empty,
            EventBonusPercentage.Zero
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

