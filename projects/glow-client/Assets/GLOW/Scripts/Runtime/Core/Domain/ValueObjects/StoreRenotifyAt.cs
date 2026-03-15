using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StoreRenotifyAt(ObscuredDateTimeOffset Value)
    {
        public static StoreRenotifyAt Empty { get; } = new (DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
