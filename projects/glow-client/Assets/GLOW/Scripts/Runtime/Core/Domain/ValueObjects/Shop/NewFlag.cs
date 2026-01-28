namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record NewFlag(bool Flg)
    {
        public static NewFlag Empty { get; } = new(false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
