namespace GLOW.Scenes.DiamondPurchaseHistory.Domain
{
    public record PageNumber(int Value)
    {
        public static PageNumber operator -(PageNumber a, int b)
        {
            return new(a.Value - b);
        }

        public static PageNumber operator +(PageNumber a, int b)
        {
            return new(a.Value + b);
        }

        public static bool operator <(PageNumber a, PageNumber b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(PageNumber a, PageNumber b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >(PageNumber a, int b)
        {
            return a.Value > b;
        }

        public static bool operator <(PageNumber a, int b)
        {
            return a.Value < b;
        }

    };
}
