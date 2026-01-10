namespace GLOW.Core.Domain.ValueObjects
{
    public record OutputRatio(decimal Value)
    {
        public static OutputRatio Zero { get; } = new(0);
        public static OutputRatio Empty { get; } = new(0);

        public string ToShowText()
        {
            return $"{Value:F3}%";
        }

        public bool IsZero()
        {
            return Value <= 0;
        }
    };
}
