namespace GLOW.Core.Domain.ValueObjects
{
    public record PrefixWordSortOrder(int Value)
    {
        public static PrefixWordSortOrder Empty { get; } = new PrefixWordSortOrder(-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
