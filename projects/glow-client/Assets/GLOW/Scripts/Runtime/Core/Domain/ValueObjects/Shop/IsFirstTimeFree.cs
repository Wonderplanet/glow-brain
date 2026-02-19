namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record IsFirstTimeFree(bool Value)
    {
        public static IsFirstTimeFree True { get; } = new(true);
        public static IsFirstTimeFree False { get; } = new(false);

        public bool IsEnable()
        {
            return Value;
        }

        public static implicit operator bool(IsFirstTimeFree isFirstTimeFree) => isFirstTimeFree.Value;
    }
}
