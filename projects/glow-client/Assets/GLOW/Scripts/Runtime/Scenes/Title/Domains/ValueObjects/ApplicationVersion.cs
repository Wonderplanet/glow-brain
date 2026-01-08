namespace GLOW.Scenes.Title.Domains.ValueObjects
{
    public record ApplicationVersion(string Value)
    {
        public static ApplicationVersion Empty { get; } = new(string.Empty);
        public override string ToString() => Value;
    }
}