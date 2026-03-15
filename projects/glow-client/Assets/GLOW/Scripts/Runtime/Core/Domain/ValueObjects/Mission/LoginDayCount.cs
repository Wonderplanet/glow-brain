using System;
using Cysharp.Text;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record LoginDayCount(ObscuredInt Value)
    {
        public static LoginDayCount Empty { get; } = new LoginDayCount(0);
        public static LoginDayCount Zero { get; } = new LoginDayCount(0);
        
        public bool IsZero()
        {
            return Value == 0;
        }

        public BonusPoint ToBonusPoint()
        {
            return new BonusPoint(Value);
        }
        
        public CriterionCount ToCriterionCount()
        {
            return new CriterionCount(Value);
        }
        
        public float ToGaugeRate(LoginDayCount start, LoginDayCount end, LoginDayCount interval)
        {
            return Mathf.Clamp01((Value - start.Value + interval.Value) / (float)(end.Value - start.Value + interval.Value));
        }
        
        public string ToLoginDayCountText()
        {
            return ZString.Format("{0}日目", Value);
        }
        
        public string ToStringSeparated()
        {
            return ZString.Format("{0:N0}", Value);
        }
        
        public static bool operator >(LoginDayCount a, LoginDayCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(LoginDayCount a, LoginDayCount b)
        {
            return a.Value >= b.Value;
        }

        public static bool operator <(LoginDayCount a, LoginDayCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(LoginDayCount a, LoginDayCount b)
        {
            return a.Value <= b.Value;
        }
        
        public static bool operator >(LoginDayCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(LoginDayCount a, int b)
        {
            return a.Value >= b;
        }

        public static bool operator <(LoginDayCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(LoginDayCount a, int b)
        {
            return a.Value <= b;
        }
        
        public static LoginDayCount operator -(LoginDayCount a, int b)
        {
            return new LoginDayCount(a.Value - b);
        }
        
        public static LoginDayCount operator -(LoginDayCount a, LoginDayCount b)
        {
            return new LoginDayCount(a.Value - b.Value);
        }
        
        public static LoginDayCount operator +(LoginDayCount a, int b)
        {
            return new LoginDayCount(a.Value + b);
        }
        
        public static LoginDayCount operator %(LoginDayCount a, int b)
        {
            if (b < 0) throw new Exception("b is minus number.");
            
            return new LoginDayCount(a.Value % b);
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}