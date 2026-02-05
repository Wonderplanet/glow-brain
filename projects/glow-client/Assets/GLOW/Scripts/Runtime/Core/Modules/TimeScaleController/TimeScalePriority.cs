namespace GLOW.Core.Modules.TimeScaleController
{
    public record TimeScalePriority(int Value)
    {
        public static TimeScalePriority Min = new TimeScalePriority(int.MinValue);
        
        public static bool operator <(TimeScalePriority a, TimeScalePriority b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(TimeScalePriority a, TimeScalePriority b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(TimeScalePriority a, TimeScalePriority b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(TimeScalePriority a, TimeScalePriority b)
        {
            return a.Value >= b.Value;
        }
    }
}