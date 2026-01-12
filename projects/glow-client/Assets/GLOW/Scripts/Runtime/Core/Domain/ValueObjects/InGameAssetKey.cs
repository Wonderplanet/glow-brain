namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameAssetKey(string Value)
    {
        public static InGameAssetKey Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}