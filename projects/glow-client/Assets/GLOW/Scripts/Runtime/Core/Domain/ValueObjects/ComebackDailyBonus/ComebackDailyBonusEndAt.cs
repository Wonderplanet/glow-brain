using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ComebackDailyBonus
{
    public record ComebackDailyBonusEndAt(ObscuredDateTimeOffset Value)
    {
        public static ComebackDailyBonusEndAt Empty { get; } = new (DateTimeOffset.MinValue);
        
        public static RemainingTimeSpan operator -(ComebackDailyBonusEndAt a, DateTimeOffset b)
        {
            return new RemainingTimeSpan(a.Value - b);
        }
        
        public static bool operator <(ComebackDailyBonusEndAt a, DateTimeOffset b)
        {
            return a.Value < b;
        }
        
        public static bool operator >(ComebackDailyBonusEndAt a, DateTimeOffset b)
        {
            return a.Value > b;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}