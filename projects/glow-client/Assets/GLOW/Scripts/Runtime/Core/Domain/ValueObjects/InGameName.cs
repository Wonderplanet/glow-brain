namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameName(string Value)
    {
        public static InGameName Empty { get; } = new(string.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}