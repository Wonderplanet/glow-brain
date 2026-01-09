using System;
using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyUnitNum(int Value) : IComparable<PartyUnitNum>
    {
        public static PartyUnitNum Empty { get; } = new(0);
        public static PartyUnitNum One => new(1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(PartyUnitNum other)
        {
            return Value.CompareTo(other.Value);
        }

        public static bool operator ==(PartyUnitNum left, int right)
        {
            if (left == null) return false;
            return left.Value == right;
        }

        public static bool operator !=(PartyUnitNum left, int right)
        {
            if (left == null) return true;
            return left.Value != right;
        }

        public bool IsZeroOrLess()
        {
            return Value <= 0;
        }

        public string ToStringForSpecialRule()
        {
            return ZString.Format("{0}体まで編成可能", Value);
        }
    }
}
