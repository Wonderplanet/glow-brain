using System;
using System.Globalization;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using UnityEngine;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackPower(ObscuredDecimal Value) : IComparable
    {
        public static AttackPower Empty { get; } = new(0m);
        public static AttackPower Zero { get; } = new(0m);
        public static AttackPower LowerLimitWithDebuff { get; } = new(1); // デバフによる攻撃力ダウン時の下限

        public ObscuredDecimal Value { get; } = Value > 0m ? Value : 0m;

        public static AttackPower operator * (AttackPower a, int b)
        {
            return new AttackPower(a.Value * b);
        }

        public static AttackPower operator * (AttackPower a, AttackPower b)
        {
            return new AttackPower(a.Value * b.Value);
        }

        public static AttackPower operator * (AttackPower a, Percentage b)
        {
            return new AttackPower(a.Value * b.Value / 100m);
        }

        public static AttackPower operator * (AttackPower a, PercentageM b)
        {
            return new AttackPower(a.Value * b.Value / 100m);
        }

        public static AttackPower operator * (AttackPower a, CharacterColorAdvantageAttackBonus b)
        {
            return new AttackPower(a.Value * b.ToDecimal());
        }

        public static AttackPower operator * (AttackPower a, CharacterColorAdvantageDefenseBonus b)
        {
            return new AttackPower(a.Value * b.ToDecimal());
        }

        public static AttackPower operator *(AttackPower a, HealPower b)
        {
            return new AttackPower(a.Value * b.ToRate());
        }

        public static AttackPower operator *(AttackPower a, decimal b)
        {
            return new AttackPower(a.Value * b);
        }

        public static AttackPower operator *(AttackPower a, RushCoefficient b)
        {
            return new AttackPower(a.Value * b.Value);
        }

        public static AttackPower operator + (AttackPower a, AttackPower b)
        {
            return new AttackPower(a.Value + b.Value);
        }

        public static AttackPower operator + (AttackPower a, decimal b)
        {
            return new AttackPower(a.Value + b);
        }

        public static AttackPower operator - (AttackPower a, AttackPower b)
        {
            return new AttackPower(a.Value - b.Value);
        }

        public static bool operator > (AttackPower a, AttackPower b)
        {
            return a.Value > b.Value;
        }

        public static bool operator < (AttackPower a, AttackPower b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >= (AttackPower a, AttackPower b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <= (AttackPower a, AttackPower b)
        {
            return a.Value <= b.Value;
        }

        public static AttackPower Max(AttackPower a, AttackPower b)
        {
            return a.Value >= b.Value ? a : b;
        }

        public static AttackPower Min(AttackPower a, AttackPower b)
        {
            return a.Value <= b.Value ? a : b;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public Damage ToDamage()
        {
            return new Damage( decimal.ToInt32(decimal.Ceiling(Value)));
        }

        public Heal ToHeal()
        {
            return new Heal(decimal.ToInt32(decimal.Ceiling(Value)));
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }

        public PercentageM ToRushPercentageM()
        {
            return new PercentageM(Value / 100);
        }

        public override string ToString()
        {
            return Value.ToString("", CultureInfo.InvariantCulture);
        }

        public string ToStringN0()
        {
            return decimal.Ceiling(Value).ToString("N0", CultureInfo.InvariantCulture);
        }

        public AttackPower ToCeiling()
        {
            return new AttackPower(decimal.Ceiling(Value));
        }

        public int CompareTo(object obj)
        {
            if (obj is AttackPower other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }
    }
}
