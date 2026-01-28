namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultDateTitle(string Value)
    {
        public static TitleBnIdLinkageResultDateTitle Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
