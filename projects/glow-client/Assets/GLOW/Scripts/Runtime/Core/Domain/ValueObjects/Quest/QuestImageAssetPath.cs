namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record QuestImageAssetPath(string Value)
    {
        public static QuestImageAssetPath Empty { get; } = new(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static QuestImageAssetPath GetQuestImagePath(string key)
        {
            return new QuestImageAssetPath($"quest_image_{key}");
        }
    };
}
