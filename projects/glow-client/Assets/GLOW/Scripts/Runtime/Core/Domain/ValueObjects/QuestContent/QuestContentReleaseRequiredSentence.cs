namespace GLOW.Core.Domain.ValueObjects.QuestContent
{
    public record QuestContentReleaseRequiredSentence(string Value)
    {
        public static QuestContentReleaseRequiredSentence Empty { get; } = new (string.Empty);
    }
}
