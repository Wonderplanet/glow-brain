using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyNo(ObscuredInt Value) : IComparable<PartyNo>
    {
        public static PartyNo Empty { get; } = new(0);
        public static PartyNo One => new(1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(PartyNo other)
        {
            return Value.CompareTo(other.Value);
        }

        public static bool operator ==(PartyNo left, int right)
        {
            if (left == null) return false;
            return left.Value == right;
        }

        public static bool operator !=(PartyNo left, int right)
        {
            if (left == null) return true;
            return left.Value != right;
        }

        public int ToInt()
        {
            return Value;
        }
    }
}
