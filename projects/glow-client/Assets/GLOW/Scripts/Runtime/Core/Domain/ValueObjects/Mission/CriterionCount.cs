using System;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record CriterionCount(ObscuredInt Value) : IComparable<CriterionCount>
    {
        public static CriterionCount Empty { get; } = new(0);

        public bool IsZero()
        {
            return Value == 0;
        }
        
        public static bool operator >(CriterionCount a, BonusPoint b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(CriterionCount a, BonusPoint b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(CriterionCount a, BonusPoint b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(CriterionCount a, BonusPoint b)
        {
            return a.Value <= b.Value;
        }

        public BonusPoint ToBonusPoint()
        {
            return new BonusPoint(Value);
        }

        public string ToStringSeparated()
        {
            return ZString.Format("{0:N0}", Value);
        }
        
        public int CompareTo(CriterionCount other)
        {
            if (ReferenceEquals(this, other)) return 0;
            if (ReferenceEquals(null, other)) return 1;
            return Value.CompareTo(other.Value);
        }
    }
}
