namespace GLOW.Core.Domain.ValueObjects
{
    public record TotalDiamond(int Value)
    {
        public static TotalDiamond Zero { get; } = new(0);

        public static bool operator <(TotalDiamond a, TotalDiamond b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(TotalDiamond a, TotalDiamond b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(TotalDiamond a, TotalDiamond b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(TotalDiamond a, TotalDiamond b)
        {
            return a.Value >= b.Value;
        }

        public string ToStringSeparated()
        {
            return Value.ToString("N0");
        }
    }
}
