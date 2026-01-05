using System;
using System.Globalization;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventBonusPercentage(ObscuredInt Value) : IComparable
    {
        public static EventBonusPercentage Empty { get; } = new (0);
        public static EventBonusPercentage Zero => new (0);
        public static EventBonusPercentage OneHundred => new (100);

        public PercentageM ToPercentageM() => new PercentageM(Value);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(object obj)
        {
            if (obj is EventBonusPercentage other)
            {
                return Value.CompareTo(other.Value);
            }
            return 0;
        }
        public static bool operator < (EventBonusPercentage left, EventBonusPercentage right)
        {
            return left.CompareTo(right) < 0;
        }

        public static bool operator > (EventBonusPercentage left, EventBonusPercentage right)
        {
            return left.CompareTo(right) > 0;
        }

        public static bool operator <= (EventBonusPercentage left, EventBonusPercentage right)
        {
            return left.CompareTo(right) <= 0;
        }

        public static bool operator >=(EventBonusPercentage left, EventBonusPercentage right)
        {
            return left.CompareTo(right) >= 0;
        }

        public string GetMultiplierText()
        {
            return ZString.Format("報酬{0}倍!!", (100 + Value) / 100f);
        }

        public static EventBonusPercentage operator +(EventBonusPercentage a, EventBonusPercentage b)
        {
            return new EventBonusPercentage(a.Value + b.Value);
        }

        public static EventBonusPercentage operator -(EventBonusPercentage a, EventBonusPercentage b)
        {
            return new EventBonusPercentage(a.Value - b.Value);
        }

        public string ToStringRatio()
        {
            var v = (100 + Value) / 100f;
            return v.ToString(CultureInfo.InvariantCulture);
        }
    }
}
