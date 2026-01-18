namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceIconAssetPath(string Value)
    {
        public static PlayerResourceIconAssetPath Empty { get; } = new PlayerResourceIconAssetPath(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
