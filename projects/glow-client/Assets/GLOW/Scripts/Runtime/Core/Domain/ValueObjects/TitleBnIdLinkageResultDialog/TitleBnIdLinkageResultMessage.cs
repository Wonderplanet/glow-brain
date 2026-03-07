namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultMessage(string Value)
    {
        public static TitleBnIdLinkageResultMessage Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
