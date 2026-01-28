using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public record QuestSelectContentUnlockDescriptionStatus(
        bool IsOpened,
        RemainingTimeSpan RemainingTimeSpan,
        QuestName RequiredQuestName)
    {
        public static QuestSelectContentUnlockDescriptionStatus Empty { get; }=
            new QuestSelectContentUnlockDescriptionStatus(
                false,
                RemainingTimeSpan.Empty,
                new QuestName(string.Empty)
            );
    };
}
