using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PercentageM(decimal Value)
    {
        public static PercentageM Empty { get; } = new(0m);
        public static PercentageM Zero { get; } = new(0m);
        public static PercentageM Hundred { get; } = new(100m);

        public static PercentageM operator +(PercentageM a, PercentageM b)
        {
            return new PercentageM(a.Value + b.Value);
        }

        public static PercentageM operator -(PercentageM a, PercentageM b)
        {
            return new PercentageM(a.Value - b.Value);
        }

        public static bool operator <(PercentageM a, PercentageM b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(PercentageM a, PercentageM b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <=(PercentageM a, PercentageM b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(PercentageM a, PercentageM b)
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

        public decimal ToRate()
        {
            return Value / 100;
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public string ToStringF2()
        {
            return Value.ToString("F2");
        }

        public float ToInverseRate()
        {
            return Value == 0 ? float.MaxValue : (float)(100 / Value);
        }

        /// <summary>
        /// 足して100%になる％値を返す
        /// </summary>
        /// <returns></returns>
        public PercentageM ComplementSet()
        {
            return new PercentageM(100 - Value);
        }
    }
}
