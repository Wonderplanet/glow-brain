namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameNumber(int Value)
    {
        public static InGameNumber Empty { get; } = new(0);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}