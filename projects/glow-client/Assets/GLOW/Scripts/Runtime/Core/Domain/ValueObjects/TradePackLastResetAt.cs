using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TradePackLastResetAt(ObscuredDateTimeOffset Value)
    {
        public static TradePackLastResetAt Empty { get; } = new (DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
