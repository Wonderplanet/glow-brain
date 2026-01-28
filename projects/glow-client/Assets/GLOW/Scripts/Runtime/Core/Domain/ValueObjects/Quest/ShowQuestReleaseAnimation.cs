namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record ShowQuestReleaseAnimation(bool ShouldShow,
        QuestName QuestName,
        QuestImageAssetPath QuestImageAssetPath,
        QuestFlavorText FlavorText)
    {
        public static ShowQuestReleaseAnimation Empty { get; } =
            new ShowQuestReleaseAnimation(
                false,
                new QuestName(""),
                new QuestImageAssetPath(""),
                new QuestFlavorText(""));
    };
}
