using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TradeAmountResetAt(ObscuredDateTimeOffset Value)
    {
        public static TradeAmountResetAt Empty { get; } = new(DateTimeOffset.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}