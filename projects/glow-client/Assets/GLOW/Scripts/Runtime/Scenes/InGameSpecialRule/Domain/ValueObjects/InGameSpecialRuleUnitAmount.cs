using System;
using System.Globalization;
using Cysharp.Text;
namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    /// <summary> 編成キャラクター数 </summary>
    public record InGameSpecialRuleUnitAmount(int Value) : IComparable
    {
        public static InGameSpecialRuleUnitAmount Zero { get; } = new InGameSpecialRuleUnitAmount(0);

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsZeroOrLess()
        {
            return Value <= 0;
        }

        public static bool operator < (InGameSpecialRuleUnitAmount a, InGameSpecialRuleUnitAmount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <= (InGameSpecialRuleUnitAmount a, InGameSpecialRuleUnitAmount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (InGameSpecialRuleUnitAmount a, InGameSpecialRuleUnitAmount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >= (InGameSpecialRuleUnitAmount a, InGameSpecialRuleUnitAmount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator < (InGameSpecialRuleUnitAmount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <= (InGameSpecialRuleUnitAmount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator > (InGameSpecialRuleUnitAmount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >= (InGameSpecialRuleUnitAmount a, int b)
        {
            return a.Value >= b;
        }

        public int CompareTo(object obj)
        {
            if (obj is InGameSpecialRuleUnitAmount other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }

        public static implicit operator int(InGameSpecialRuleUnitAmount value) => value.Value;

        public string ToStringForSpecialRule()
        {
            return ZString.Format("{0}体まで編成可能", Value);
        }
    }
}