namespace GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog
{
    public record TitleBnIdLinkageResultRightButtonTitle(string Value)
    {
        public static TitleBnIdLinkageResultRightButtonTitle Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
