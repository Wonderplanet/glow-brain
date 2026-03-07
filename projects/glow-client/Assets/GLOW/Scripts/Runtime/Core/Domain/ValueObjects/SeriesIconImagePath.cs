namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesIconImagePath(string Value)
    {
        public static SeriesIconImagePath Empty { get; } = new(string.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
