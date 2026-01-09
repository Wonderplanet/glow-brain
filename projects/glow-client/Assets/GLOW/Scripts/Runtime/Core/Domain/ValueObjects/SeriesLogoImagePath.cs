namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesLogoImagePath(string Value)
    {
        public static SeriesLogoImagePath Empty { get; } = new SeriesLogoImagePath(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
