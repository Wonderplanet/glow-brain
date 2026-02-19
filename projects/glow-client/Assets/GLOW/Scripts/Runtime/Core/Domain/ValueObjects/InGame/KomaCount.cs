namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaCount(int Value)
    {
        public static KomaCount Empty { get; } = new(0);

        public static KomaCount operator +(KomaCount a) => a;
        public static KomaCount operator -(KomaCount a) => new KomaCount(-a.Value);

        public static KomaCount operator +(KomaCount a, KomaCount b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaCount(a.Value + b.Value);
        }
        
        public static KomaCount operator +(KomaCount a, int b)
        {
            if (a.IsEmpty()) return Empty;
            return new KomaCount(a.Value + b);
        }

        public static KomaCount operator -(KomaCount a, KomaCount b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaCount(a.Value - b.Value);
        }

        public static bool operator >=(KomaCount a, KomaCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(KomaCount a, KomaCount b)
        {
            return a.Value <= b.Value;
        }
        
        public static bool operator >(KomaCount a, KomaCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(KomaCount a, KomaCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(KomaCount a, int b)
        {
            return a.Value >= b;
        }

        public static bool operator <=(KomaCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(KomaCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator <(KomaCount a, int b)
        {
            return a.Value < b;
        }
        
        public static KomaCount Max(KomaCount a, KomaCount b)
        {
            return a >= b ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
