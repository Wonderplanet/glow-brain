namespace GLOW.Core.Presentation.ValueObjects
{
    public record AnimationPlayerTime(double Value)
    {
        public static AnimationPlayerTime Empty { get; } = new(0);

        public static bool operator <(AnimationPlayerTime a, double b)
        {
            return a.Value < b;
        }

        public static bool operator <=(AnimationPlayerTime a, double b)
        {
            return a.Value <= b;
        }

        public static bool operator >(AnimationPlayerTime a, double b)
        {
            return a.Value > b;
        }

        public static bool operator >=(AnimationPlayerTime a, double b)
        {
            return a.Value >= b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
