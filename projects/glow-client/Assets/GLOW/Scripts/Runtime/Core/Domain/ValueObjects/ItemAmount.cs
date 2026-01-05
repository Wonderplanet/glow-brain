using System.Globalization;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemAmount(ObscuredInt Value)
    {
        public static ItemAmount Empty { get; } = new ItemAmount(0);
        public static ItemAmount Zero { get; } = new ItemAmount(0);
        public static ItemAmount One { get; } = new ItemAmount(1);
        public static ItemAmount Infinity { get; } = new ItemAmount(int.MaxValue);

        public bool IsMinus()
        {
            return Value < 0;
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public static ItemAmount operator +(ItemAmount a, int b)
        {
            return new ItemAmount(a.Value + b);
        }

        public static ItemAmount operator -(ItemAmount a, int b)
        {
            return new ItemAmount(a.Value - b);
        }

        public static ItemAmount operator +(ItemAmount a, ItemAmount b)
        {
            return new ItemAmount(a.Value + b.Value);
        }

        public static ItemAmount operator -(ItemAmount a, ItemAmount b)
        {
            return new ItemAmount(a.Value - b.Value);
        }

        public static ItemAmount operator *(ItemAmount a, ItemAmount b)
        {
            return new ItemAmount(a.Value * b.Value);
        }

        public static ItemAmount operator /(ItemAmount a, ItemAmount b)
        {
            return new ItemAmount(a.Value / b.Value);
        }

        public static ItemAmount operator *(ItemAmount a, TradeCostAmount b)
        {
            return new ItemAmount(a.Value * b.Value);
        }

        public static bool operator >(ItemAmount a, ItemAmount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator <(ItemAmount a, ItemAmount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >=(ItemAmount a, ItemAmount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <=(ItemAmount a, ItemAmount b)
        {
            return a.Value <= b.Value;
        }

        public static ItemAmount Min(ItemAmount a, ItemAmount b)
        {
            return a.Value <= b.Value ? a : b;
        }

        public static ItemAmount Max(ItemAmount a, ItemAmount b)
        {
            return a.Value >= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return ReferenceEquals(this, Zero) || Value == 0;
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public string ToStringSeparated()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public string ToStringWithMultiplication()
        {
            return ZString.Format("×{0}", Value);
        }

        public string ToStringWithMultiplicationSeparated()
        {
            return ZString.Format("×{0}", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public TradableAmount ToTradableAmount()
        {
            return new TradableAmount(Value);
        }

        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Value);
        }

        public ItemAmount Clamp(ItemAmount min, ItemAmount max)
        {
            if (this >= min && this <= max) return this;

            return new ItemAmount(Mathf.Clamp(Value, min.Value, max.Value));
        }
    }
}
