using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    /// <summary> ヒーローゲートの初期HP </summary>
    public record InGameSpecialRuleStartOutpostHp(int Value) : IComparable
    {
        public static InGameSpecialRuleStartOutpostHp Zero { get; } = new InGameSpecialRuleStartOutpostHp(0);

        public bool IsZero()
        {
            return Value == 0;
        }

        public static bool operator < (InGameSpecialRuleStartOutpostHp a, InGameSpecialRuleStartOutpostHp b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <= (InGameSpecialRuleStartOutpostHp a, InGameSpecialRuleStartOutpostHp b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator > (InGameSpecialRuleStartOutpostHp a, InGameSpecialRuleStartOutpostHp b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >= (InGameSpecialRuleStartOutpostHp a, InGameSpecialRuleStartOutpostHp b)
        {
            return a.Value >= b.Value;
        }

        public int CompareTo(object obj)
        {
            if (obj is InGameSpecialRuleStartOutpostHp other)
            {
                return Value.CompareTo(other.Value);
            }

            return 1;
        }

        public HP ToHp()
        {
            return new HP(Value);
        }
    }
}
