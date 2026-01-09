namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultLeftButtonTitle(string Value)
    {
        public static TitleBnIdLinkageResultLeftButtonTitle Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
