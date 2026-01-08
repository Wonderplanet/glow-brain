namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record IsFirstTimeFreeDisplay(bool Value)
    {
        public static IsFirstTimeFreeDisplay True { get; } = new(true);
        public static IsFirstTimeFreeDisplay False { get; } = new(false);

        public bool IsEnable()
        {
            return Value;
        }
    }
}
