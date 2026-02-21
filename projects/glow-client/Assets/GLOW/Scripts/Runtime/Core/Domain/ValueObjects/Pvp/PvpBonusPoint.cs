using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpBonusPoint(ObscuredInt Value) : IComparable
    {
        public static PvpBonusPoint Empty { get; } = new PvpBonusPoint(0);
        public static PvpBonusPoint Zero { get; } = new PvpBonusPoint(0);
        
        public static bool operator >(PvpBonusPoint a, PvpBonusPoint b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(PvpBonusPoint a, PvpBonusPoint b)
        {
            return a.Value < b.Value;
        }
        
        public static bool operator >=(PvpBonusPoint a, PvpBonusPoint b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <=(PvpBonusPoint a, PvpBonusPoint b)
        {
            return a.Value <= b.Value;
        }
        
        public static PvpBonusPoint operator +(PvpBonusPoint a, PvpBonusPoint b)
        {
            return new PvpBonusPoint(a.Value + b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int CompareTo(object obj)
        {
            if (obj is PvpBonusPoint other)
            {
                return Value.CompareTo(other.Value);
            }
            return -1;
        }
    }
}