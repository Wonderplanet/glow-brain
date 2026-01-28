using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record Percentage(ObscuredInt Value)
    {
        public static Percentage Empty { get; } = new(0);
        public static Percentage Zero { get; } = new(0);
        public static Percentage One { get; } = new(1);
        public static Percentage Hundred { get; } = new(100);

        public static Percentage operator +(Percentage a, Percentage b)
        {
            return new Percentage(a.Value + b.Value);
        }

        public static Percentage operator -(Percentage a, Percentage b)
        {
            return new Percentage(a.Value - b.Value);
        }

        public static Percentage operator *(Percentage a, Percentage b)
        {
            return new Percentage(a.Value * b.Value);
        }
        
        public static bool operator >=(Percentage a, Percentage b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <=(Percentage a, Percentage b)
        {
            return a.Value <= b.Value;
        }

        public static Percentage Max(Percentage a, Percentage b)
        {
            return a.Value >= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public float ToRate()
        {
            return Value / 100f;
        }

        public float ToInverseRate()
        {
            return Value == 0 ? float.MaxValue : 100f / Value;
        }

        /// <summary>
        /// 足して100%になる％値を返す
        /// </summary>
        /// <returns></returns>
        public Percentage ComplementSet()
        {
            return new Percentage(100 - Value);
        }
    }
}
