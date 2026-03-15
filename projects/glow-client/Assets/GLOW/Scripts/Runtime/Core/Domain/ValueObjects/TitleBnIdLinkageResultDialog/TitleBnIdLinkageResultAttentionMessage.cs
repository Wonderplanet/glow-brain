namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultAttentionMessage(string Value)
    {
        public static TitleBnIdLinkageResultAttentionMessage Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
