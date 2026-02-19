
using System;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record HP(ObscuredInt Value) : IComparable
    {
        public static HP Empty { get; } = new(0);
        public static HP Zero { get; } = new(0);
        public static HP One { get; } = new(1);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public int Digit
        {
            get
            {
                var textString = Value.ToString();
                return textString.Length;
            }
        }

        public static HP operator +(HP a, HP b)
        {
            return new HP(a.Value + b.Value);
        }

        public static HP operator -(HP a, Damage b)
        {
            return new HP(a.Value - b.Value);
        }

        public static HP operator +(HP a, Damage b)
        {
            return new HP(a.Value + b.Value);
        }

        public static HP operator +(HP a, Heal b)
        {
            return new HP(a.Value + b.Value);
        }

        public static HP operator -(HP a, Heal b)
        {
            return new HP(a.Value - b.Value);
        }

        public static HP operator -(HP a, HP b)
        {
            return new HP(a.Value - b.Value);
        }

        public static HP operator *(HP a, float b)
        {
            return new HP((int)(a.Value * b));
        }

        public static HP operator *(HP a, Percentage b)
        {
            return new HP(Mathf.FloorToInt(a.Value * b.ToRate()));
        }

        public static HP operator *(HP a, PercentageM b)
        {
            return new HP((int)Math.Floor(a.Value * b.Value / 100m));
        }

        public static bool operator <(HP a, HP b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(HP a, HP b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(HP a, HP b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(HP a, HP b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(HP a, Damage b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(HP a, Damage b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(HP a, Damage b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(HP a, Damage b)
        {
            return a.Value >= b.Value;
        }

        public static HP Min(HP a, HP b)
        {
            return a.Value < b.Value ? a : b;
        }

        public override string ToString()
        {
            return Value.ToString("N0", null);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public Percentage PercentageTo(HP hp)
        {
            if (hp.IsEmpty()) return Percentage.Empty;

            return new Percentage(Mathf.FloorToInt(Value / (float)hp.Value * 100f));
        }

        public AttackPower ToAttackPower()
        {
            return new AttackPower((decimal)Value);
        }

        public Damage ToDamage()
        {
            return new Damage(Value);
        }

        public Heal ToHeal()
        {
            return new Heal(Value);
        }

        public int CompareTo(object obj)
        {
            if (obj is HP other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
