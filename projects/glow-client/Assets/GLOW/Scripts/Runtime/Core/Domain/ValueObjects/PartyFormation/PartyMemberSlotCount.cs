using System;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyMemberSlotCount(ObscuredInt Value) : IComparable
    {
        public static PartyMemberSlotCount Empty { get; } = new(0);
        public static PartyMemberSlotCount Max { get; } = new(10);

        int IComparable.CompareTo(object obj)
        {
            if (obj is PartyMemberSlotCount other)
            {
                return Value.CompareTo(other.Value);
            }
            return 0;
        }

        public static bool operator <(PartyMemberSlotCount left, int right) => left.Value < right;
        public static bool operator >(PartyMemberSlotCount left, int right) => left.Value > right;
        public static bool operator <=(PartyMemberSlotCount left, int right) => left.Value <= right;
        public static bool operator >=(PartyMemberSlotCount left, int right) => left.Value >= right;
        
        public string ToPartyMemberSlotCountText()
        {
            return ZString.Format("{0}体まで編成可能！", Value);
        }
    }
}
