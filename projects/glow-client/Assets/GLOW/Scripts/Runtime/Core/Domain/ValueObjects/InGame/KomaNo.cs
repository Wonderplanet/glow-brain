using System;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaNo(int Value) : IComparable
    {
        public static KomaNo Empty { get; } = new(0);
        public static KomaNo Zero { get; } = new(0);

        public static KomaNo operator +(KomaNo a) => a;
        public static KomaNo operator -(KomaNo a) => new KomaNo(-a.Value);

        public static KomaCount operator +(KomaNo a, KomaNo b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return KomaCount.Empty;
            return new KomaCount(a.Value + b.Value);
        }
        
        public static KomaNo operator +(KomaNo a, KomaCount b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaNo(a.Value + b.Value);
        }

        public static KomaCount operator -(KomaNo a, KomaNo b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return KomaCount.Empty;
            return new KomaCount(a.Value - b.Value);
        }
        
        public static KomaNo operator -(KomaNo a, KomaCount b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaNo(a.Value - b.Value);
        }
        
        public static KomaNo operator +(KomaNo a, int b)
        {
            if (a.IsEmpty()) return Empty;
            return new KomaNo(a.Value + b);
        }

        public static KomaNo operator -(KomaNo a, int b)
        {
            if (a.IsEmpty()) return Empty;
            return new KomaNo(a.Value - b);
        }

        public static bool operator >=(KomaNo a, KomaNo b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(KomaNo a, KomaNo b)
        {
            return a.Value <= b.Value;
        }
        
        public static bool operator >(KomaNo a, KomaNo b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(KomaNo a, KomaNo b)
        {
            return a.Value < b.Value;
        }

        public KomaCount ToKomaCount()
        {
            return new KomaCount(Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public int CompareTo(object obj)
        {
            if (obj is KomaNo other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
