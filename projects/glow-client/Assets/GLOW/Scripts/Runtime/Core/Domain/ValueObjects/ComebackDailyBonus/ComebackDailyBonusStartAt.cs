using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ComebackDailyBonus
{
    public record ComebackDailyBonusStartAt(ObscuredDateTimeOffset Value)
    {
        public static ComebackDailyBonusStartAt Empty { get; } = new (DateTimeOffset.MinValue);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}