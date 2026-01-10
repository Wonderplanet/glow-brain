namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record DrawCountThresholdStepNumber(int Value)
    {
        public static DrawCountThresholdStepNumber Empty { get; } = new(-1);
        public static DrawCountThresholdStepNumber Zero { get; } = new(0);


        public int Value { get; } = Value > 0 ? Value : 0;

        public static bool operator <(DrawCountThresholdStepNumber a, DrawCountThresholdStepNumber b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(DrawCountThresholdStepNumber a, DrawCountThresholdStepNumber b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(DrawCountThresholdStepNumber a, DrawCountThresholdStepNumber b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(DrawCountThresholdStepNumber a, DrawCountThresholdStepNumber b)
        {
            return a.Value >= b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
