using System;
using Cysharp.Text;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record BonusPoint(ObscuredInt Value) : IComparable<BonusPoint>
    {
        public static BonusPoint Empty { get; } = new BonusPoint(0);
        
        public static BonusPoint operator +(BonusPoint a, BonusPoint b)
        {
            return new BonusPoint(a.Value + b.Value);
        }
        
        public static BonusPoint operator +(BonusPoint a, int b)
        {
            return new BonusPoint(a.Value + b);
        }
        
        public static bool operator >(BonusPoint a, CriterionCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(BonusPoint a, CriterionCount b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <(BonusPoint a, CriterionCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(BonusPoint a, CriterionCount b)
        {
            return a.Value <= b.Value;
        }

        public float ToGaugeRate(BonusPoint maxBonusPoint)
        {
            return Mathf.Clamp01(Value / (float)maxBonusPoint.Value);
        }
        
        public string ToStringSeparated()
        {
            return ZString.Format("{0:N0}", Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Value);
        }

        public int CompareTo(BonusPoint other)
        {
            if (ReferenceEquals(this, other)) return 0;
            if (ReferenceEquals(null, other)) return 1;
            return Value.CompareTo(other.Value);
        }
    }
}