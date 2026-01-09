namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record EnableAdPlay(bool Value)
    {
        public static EnableAdPlay True { get; } = new(true);
        public static EnableAdPlay False { get; } = new(false);
        public bool IsEnable()
        {
            return Value;
        }
    }
}
