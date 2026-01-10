using System;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record Coin(ObscuredLong Value): ILimitedAmountValueObject
    {
        public static Coin Empty { get; } = new (0);
        public static Coin Zero { get; } = new (0);

        public static ItemName GetItemName()
        {
            return new ItemName("コイン");
        }

        public int HasAmount => (int)Value;

        public static bool operator ==(Coin a, long b)
        {
            return a.Value == b;
        }

        public static bool operator !=(Coin a, long b)
        {
            return a.Value != b;
        }

        public static Coin operator +(Coin a, Coin b)
        {
            return new Coin(a.Value + b.Value);
        }

        public static Coin operator -(Coin a, Coin b)
        {
            return new Coin(a.Value - b.Value);
        }

        public static Coin operator -(Coin a, CostAmount b)
        {
            return new Coin(a.Value - (int)b.Value);
        }

        public static bool operator <=(Coin a, Coin b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >=(Coin a, Coin b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(Coin a, Coin b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(Coin a, Coin b)
        {
            return a.Value > b.Value;
        }
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string ToStringSeparated()
        {
            return HasAmount.ToString("N0");
        }

        public bool IsMinus()
        {
            return Value < 0;
        }

        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Convert.ToInt32(Value));
        }

        public ItemAmount ToItemAmount()
        {
            return new ItemAmount(Convert.ToInt32(Value));
        }
    }

}
