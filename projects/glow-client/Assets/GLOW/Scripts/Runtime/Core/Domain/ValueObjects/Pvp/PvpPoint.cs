using System;
using System.Globalization;
using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpPoint(ObscuredLong Value) : IComparable
    {
        public static PvpPoint Empty { get; } = new PvpPoint(0);
        public static PvpPoint Zero { get; } = new PvpPoint(0);
        
        public static bool operator >(PvpPoint a, PvpPoint b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(PvpPoint a, PvpPoint b)
        {
            return a.Value < b.Value;
        }
        
        public static bool operator >=(PvpPoint a, PvpPoint b)
        {
            return a.Value >= b.Value;
        }
        
        public static bool operator <=(PvpPoint a, PvpPoint b)
        {
            return a.Value <= b.Value;
        }
        
        public static PvpPoint operator +(PvpPoint a, PvpPoint b)
        {
            return new PvpPoint(a.Value + b.Value);
        }
        
        public static PvpPoint operator -(PvpPoint a, PvpPoint b)
        {
            return new PvpPoint(a.Value - b.Value);
        }
        
        public static PvpPoint Min(PvpPoint a, PvpPoint b)
        {
            return a.Value < b.Value ? a : b;
        }
        
        public static PvpPoint Max(PvpPoint a, PvpPoint b)
        {
            return a.Value > b.Value ? a : b;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsZero()
        {
            return Value == 0;
        }
        
        public bool IsMinus()
        {
            return Value < 0;
        }

        public string ToStringWithSeparate()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }
        
        public string ToDisplayString()
        {
            if (IsEmpty())
            {
                return "---,---,---,--- pt";
            }

            return ZString.Format("{0} pt", Value.ToString("N0", CultureInfo.InvariantCulture));
        }
        
        public string ToStringSeparate()
        {
            if (IsEmpty())
            {
                return "---,---,---,---";
            }

            return ZString.Format("{0}", Value.ToString("N0", CultureInfo.InvariantCulture));
        }

        public int CompareTo(object obj)
        {
            if (obj is PvpPoint other)
            {
                return Value.CompareTo(other.Value);
            }
            return 0;
        }
    }
}